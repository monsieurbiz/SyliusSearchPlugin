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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use Elastica\Aggregation\AbstractAggregation;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation\AggregationBuilderInterface;
use RuntimeException;

class AggregationBuilder
{
    /**
     * @var iterable<AggregationBuilderInterface>
     */
    private iterable $aggregationBuilders;

    public function __construct(iterable $aggregationBuilders)
    {
        $this->aggregationBuilders = $aggregationBuilders;
    }

    public function buildAggregations(array $aggregations, array $filters): array
    {
        $buckets = [];

        foreach ($aggregations as $aggregation) {
            $aggregationQuery = $this->buildAggregation($aggregation, $filters);
            if (false === $aggregationQuery) {
                continue;
            }
            $buckets[] = $aggregationQuery;
        }

        return array_filter($buckets);
    }

    /**
     * @param string|array $aggregation
     *
     * @return AbstractAggregation|bool
     */
    private function buildAggregation($aggregation, array $filters)
    {
        // Don't build aggregation if the given one is empty
        if (empty($aggregation)) {
            return false;
        }

        foreach ($this->aggregationBuilders as $aggregationBuilder) {
            $aggregationQuery = $aggregationBuilder->build($aggregation, $filters);
            if (null !== $aggregationQuery) {
                return $aggregationQuery;
            }
        }

        throw new RuntimeException('Aggregation can be build'); // it's throw an exception if we have not filtreable attribute
    }
}
