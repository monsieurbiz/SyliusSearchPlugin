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
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;

class ProductAttributeAggregation implements AggregationBuilderInterface
{
    public function build($aggregation, array $filters)
    {
        /** @var ProductAttributeInterface&SearchableInterface $aggregation */
        if (!$this->isSupport($aggregation) || !$aggregation->isFilterable()) {
            return null;
        }

        $qb = new \Elastica\QueryBuilder();

        $filters = array_filter($filters, function($key) use ($aggregation) {
            return false !== strpos($key, 'attributes.') && 'attributes.' . $aggregation->getCode() !== $key;
        }, \ARRAY_FILTER_USE_KEY);
        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        $qb = new \Elastica\QueryBuilder();

        return $qb->aggregation()->filter($aggregation->getCode())
            ->setFilter($filterQuery)
            ->addAggregation(
                $qb->aggregation()->nested($aggregation->getCode(), sprintf('attributes.%s', $aggregation->getCode()))
                    ->addAggregation(
                        $qb->aggregation()->terms('names')
                            ->setField(sprintf('attributes.%s.name', $aggregation->getCode()))
                            ->addAggregation(
                                $qb->aggregation()->terms('values')
                                    ->setField(sprintf('attributes.%s.value.keyword', $aggregation->getCode()))
                            )
                    )
            )
        ;
    }

    private function isSupport($aggregation): bool
    {
        return $aggregation instanceof ProductAttributeInterface;
    }
}
