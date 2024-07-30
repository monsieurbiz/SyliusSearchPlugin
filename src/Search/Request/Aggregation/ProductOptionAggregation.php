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
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;

final class ProductOptionAggregation implements AggregationBuilderInterface
{
    private bool $enableStockFilter;

    public function __construct(bool $enableStockFilter)
    {
        $this->enableStockFilter = $enableStockFilter;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param mixed $aggregation
     */
    public function build($aggregation, array $filters)
    {
        /** @var ProductOptionInterface&SearchableInterface $aggregation */
        if (!$this->isSupport($aggregation) || !$aggregation->isFilterable()) {
            return null;
        }

        $qb = new QueryBuilder();

        $filters = array_filter($filters, function (AbstractQuery $filter) use ($aggregation): bool {
            /** @var string $path */
            $path = $filter->hasParam('path') ? $filter->getParam('path') : '';

            return !$filter->hasParam('path') || (
                false !== strpos($path, 'options.')
                    && 'options.' . $aggregation->getCode() . '.values' !== $path
            );
        });

        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        $qb = new QueryBuilder();
        $optionBoolConditions = $qb->query()->bool()
                ->addMust($qb->query()->term([\sprintf('options.%s.values.enabled', $aggregation->getCode()) => ['value' => true]]))
        ;
        if ($this->enableStockFilter) {
            $optionBoolConditions->addMust($qb->query()->term([\sprintf('options.%s.values.is_in_stock', $aggregation->getCode()) => ['value' => true]]));
        }
        $valuesAggregation = $qb->aggregation()->filter('values', $optionBoolConditions)
            ->addAggregation(
                $qb->aggregation()->terms('values')
                    ->setField(\sprintf('options.%s.values.value.keyword', $aggregation->getCode()))
            )
        ;

        /** @phpstan-ignore-next-line */
        return $qb->aggregation()->filter($aggregation->getCode())
            ->setFilter($filterQuery)
            ->addAggregation(
                /** @phpstan-ignore-next-line */
                $qb->aggregation()->nested($aggregation->getCode(), \sprintf('options.%s', $aggregation->getCode()))
                    ->addAggregation(
                        $qb->aggregation()->terms('names')
                            ->setField(\sprintf('options.%s.name', $aggregation->getCode()))
                            ->addAggregation(
                                $qb->aggregation()->nested('values', \sprintf('options.%s.values', $aggregation->getCode()))
                                    ->addAggregation(
                                        $valuesAggregation
                                    )
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
        return $aggregation instanceof ProductOptionInterface && null !== $aggregation->getCode();
    }
}
