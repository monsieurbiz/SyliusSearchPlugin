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

use Elastica\Aggregation\AbstractAggregation;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;

class ProductOptionAggregation implements AggregationBuilderInterface
{
    public function build($aggregation, array $filters)
    {
        /** @var ProductOptionInterface&SearchableInterface $aggregation */
        if (!$this->isSupport($aggregation) || !$aggregation->isFilterable()) {
            return null;
        }

        $qb = new QueryBuilder();

        $filters = array_filter($filters, function($key) use ($aggregation): bool {
            return false !== strpos($key, 'options.') && 'options.' . $aggregation->getCode() !== $key;
        }, \ARRAY_FILTER_USE_KEY);
        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        $qb = new QueryBuilder();

        return $qb->aggregation()->filter($aggregation->getCode())
            ->setFilter($filterQuery)
            ->addAggregation(
                $qb->aggregation()->nested($aggregation->getCode(), sprintf('variants.options.%s', $aggregation->getCode()))
                    ->addAggregation(
                        $qb->aggregation()->terms('names')
                            ->setField(sprintf('variants.options.%s.name', $aggregation->getCode()))
                            ->addAggregation(
                                $qb->aggregation()->terms('values')
                                    ->setField(sprintf('variants.options.%s.value.keyword', $aggregation->getCode()))
                            )
                    )
            )
        ;
    }

    private function isSupport($aggregation): bool
    {
        return $aggregation instanceof ProductOptionInterface;
    }
}
