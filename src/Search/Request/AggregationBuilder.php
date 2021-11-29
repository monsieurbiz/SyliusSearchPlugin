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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use Elastica\Aggregation\AbstractAggregation;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation\AggregationBuilderInterface;

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
            $buckets[] = $this->buildAggregation($aggregation, $filters);
        }

        return array_filter($buckets);
    }

    /**
     * @param string|array $aggregation
     */
    private function buildAggregation($aggregation, array $filters): AbstractAggregation
    {
        foreach ($this->aggregationBuilders as $aggregationBuilder) {
            $aggregationQuery = $aggregationBuilder->build($aggregation, $filters);
            if (null !== $aggregationQuery) {
                return $aggregationQuery;
            }
        }

        throw new \RuntimeException('Aggregation can be build');
    }
}
