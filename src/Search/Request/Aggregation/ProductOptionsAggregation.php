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

use Elastica\Query\AbstractQuery;
use Elastica\QueryBuilder;
use Sylius\Component\Product\Model\ProductOptionInterface;

final class ProductOptionsAggregation implements AggregationBuilderInterface
{
    private ProductOptionAggregation $productOptionAggregationBuilder;

    public function __construct(ProductOptionAggregation $productOptionAggregationBuilder)
    {
        $this->productOptionAggregationBuilder = $productOptionAggregationBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param mixed $aggregation
     */
    public function build($aggregation, array $filters)
    {
        if (!$this->isSupport($aggregation)) {
            return null;
        }

        $qb = new QueryBuilder();
        $currentFilters = array_filter($filters, function (AbstractQuery $filter): bool {
            return !$filter->hasParam('path') || false === strpos($filter->getParam('path'), 'options.');
        });
        $filterQuery = $qb->query()->bool();
        foreach ($currentFilters as $filter) {
            $filterQuery->addMust($filter);
        }

        $optionsAggregation = $qb->aggregation()->nested('options', 'options');
        foreach ($aggregation as $subAggregation) {
            $subAggregationObject = $this->productOptionAggregationBuilder->build($subAggregation, $filters);
            if (null === $subAggregationObject || false === $subAggregationObject) {
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

    /**
     * @param string|array|object $aggregation
     */
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
