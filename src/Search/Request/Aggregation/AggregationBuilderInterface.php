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

interface AggregationBuilderInterface
{
    /**
     * @param string|array $aggregation
     *
     * @return AbstractAggregation|bool|null
     */
    public function build($aggregation, array $filters);
}
