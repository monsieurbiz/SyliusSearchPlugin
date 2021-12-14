<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation;

use Sylius\Component\Core\Model\TaxonInterface;

class TaxonsAggregation implements AggregationBuilderInterface
{
    public function build($aggregation, array $filters)
    {
        if (!$this->isSupported($aggregation)) {
            return null;
        }
        /** @var TaxonInterface $currentTaxon */
        $currentTaxon = $aggregation['taxons'];
        $qb = new \Elastica\QueryBuilder();

        $filters = array_filter($filters, function($key) {
            return false === strpos($key, 'taxons');
        }, \ARRAY_FILTER_USE_KEY);
        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        return $qb->aggregation()
            ->filter('taxons')
            ->setFilter($filterQuery)
            ->addAggregation(
                $qb->aggregation()
                    ->nested('taxons', 'product_taxons')
                    ->addAggregation(
                        $qb->aggregation()
                            ->nested('taxons', 'product_taxons.taxon')
                            ->addAggregation(
                                $qb->aggregation()
                                    ->filter('taxons', $qb->query()->term(['product_taxons.taxon.level' => ['value' => $currentTaxon->getLevel() + 1]]))
                                    ->addAggregation(
                                        $qb->aggregation()
                                            ->terms('codes')
                                            ->setField('product_taxons.taxon.code')
                                            ->addAggregation(
                                                $qb->aggregation()->terms('names')
                                                    ->setField('product_taxons.taxon.name')
                                            )
                                    )
                            )
                    )
            )
            ;
    }

    protected function isSupported($aggregation): bool
    {
        return \array_key_exists('taxons', $aggregation);
    }
}
