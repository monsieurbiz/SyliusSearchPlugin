<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation;

use Elastica\QueryBuilder;
use Sylius\Component\Core\Model\TaxonInterface;

final class TaxonsAggregation implements AggregationBuilderInterface
{
    public function build($aggregation, array $filters)
    {
        if (!$this->isSupported($aggregation)) {
            return null;
        }
        /** @var TaxonInterface $currentTaxon */
        $currentTaxon = $aggregation['taxons']; /** @phpstan-ignore-line */
        $qb = new QueryBuilder();

        $filters = array_filter($filters, function ($filter): bool {
            return !$filter->hasParam('path') || 'product_taxons' !== $filter->getParam('path');
        });

        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }
        $taxonLevel = $currentTaxon->getLevel() ?? 0;

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
                                    ->filter('taxons', $qb->query()->term(['product_taxons.taxon.level' => ['value' => $taxonLevel + 1]]))
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

    /**
     * @param string|array|object $aggregation
     */
    private function isSupported($aggregation): bool
    {
        return \is_array($aggregation) && \array_key_exists('taxons', $aggregation);
    }
}
