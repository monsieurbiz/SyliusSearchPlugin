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

use Sylius\Component\Product\Model\ProductOptionInterface;

class ProductOptionsAggregation implements AggregationBuilderInterface
{
    private ProductOptionAggregation $productOptionAggregationBuilder;

    public function __construct(ProductOptionAggregation $productOptionAggregationBuilder)
    {
        $this->productOptionAggregationBuilder = $productOptionAggregationBuilder;
    }

    public function build($aggregation, array $filters)
    {
        if (!$this->isSupport($aggregation)) {
            return null;
        }

        $qb = new \Elastica\QueryBuilder();

        $currentFilters = array_filter($filters, function($key): bool {
            return false === strpos($key, 'options.');
        }, \ARRAY_FILTER_USE_KEY);
        $filterQuery = $qb->query()->bool();
        foreach ($currentFilters as $filter) {
            $filterQuery->addMust($filter);
        }

        $optionsAggregation = $qb->aggregation()->nested('options', 'variants.options');
        foreach ($aggregation as $subAggregation) {
            $subAggregationObject = $this->productOptionAggregationBuilder->build($subAggregation, $filters);
            if (null === $subAggregationObject) {
                continue;
            }
            $optionsAggregation->addAggregation($subAggregationObject);
        }

        if (0 == \count($optionsAggregation->getAggs())) {
            return false;
        }

        return $qb->aggregation()->filter('options')
            ->setFilter($filterQuery)
            ->addAggregation($optionsAggregation)
        ;
    }

    private function isSupport($aggregation): bool
    {
        if (!\is_array($aggregation)) {
            return false;
        }

        foreach ($aggregation as $subAggregation) {
            if ($subAggregation instanceof ProductOptionInterface) {
                return true;
            }
        }

        return false;
    }
}
