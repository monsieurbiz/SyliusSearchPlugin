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

final class MainTaxonAggregation implements AggregationBuilderInterface
{
    public function build($aggregation, array $filters)
    {
        if (!$this->isSupported($aggregation)) {
            return null;
        }

        $qb = new QueryBuilder();
        $filters = array_filter($filters, function ($filter): bool {
            return !$filter->hasParam('path') || 'main_taxon' !== $filter->getParam('path');
        });
        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        return $qb->aggregation()
            ->filter('main_taxon')
            ->setFilter($filterQuery)
            ->addAggregation(
                $qb->aggregation()
                    ->nested('main_taxon', 'main_taxon')
                    ->addAggregation(
                        $qb->aggregation()
                            ->terms('codes')
                            ->setField('main_taxon.code')
                            ->addAggregation(
                                $qb->aggregation()
                                    ->terms('levels')
                                    ->setField('main_taxon.level')
                                    ->addAggregation(
                                        $qb->aggregation()
                                            ->terms('names')
                                            ->setField('main_taxon.name')
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
        return 'main_taxon' === $aggregation;
    }
}
