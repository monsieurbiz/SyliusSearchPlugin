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
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;

final class ProductAttributeAggregation implements AggregationBuilderInterface
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param mixed $aggregation
     */
    public function build($aggregation, array $filters)
    {
        /** @var ProductAttributeInterface&SearchableInterface $aggregation */
        if (!$this->isSupport($aggregation) || !$aggregation->isFilterable()) {
            return null;
        }

        $qb = new QueryBuilder();
        $filters = array_filter($filters, function ($filter) use ($aggregation): bool {
            return !$filter->hasParam('path') || (
                false !== strpos($filter->getParam('path'), 'attributes.')
                && 'attributes.' . $aggregation->getCode() !== $filter->getParam('path')
            );
        });

        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        /** @phpstan-ignore-next-line */
        return $qb->aggregation()->filter($aggregation->getCode())
            ->setFilter($filterQuery)
            ->addAggregation(
                /** @phpstan-ignore-next-line */
                $qb->aggregation()->nested($aggregation->getCode(), \sprintf('attributes.%s', $aggregation->getCode()))
                    ->addAggregation(
                        $qb->aggregation()->terms('names')
                            ->setField(\sprintf('attributes.%s.name', $aggregation->getCode()))
                            ->addAggregation(
                                $qb->aggregation()->terms('values')
                                    ->setField(\sprintf('attributes.%s.value.keyword', $aggregation->getCode()))
                            )
                    )
            )
        ;
    }

    /**
     * @param string|array|object $aggregation
     */
    private function isSupport($aggregation): bool
    {
        return $aggregation instanceof ProductAttributeInterface && null !== $aggregation->getCode();
    }
}
