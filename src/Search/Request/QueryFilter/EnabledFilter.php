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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class EnabledFilter implements QueryFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();

        $boolQuery->addFilter(
            $qb->query()->term(['enabled' => ['value' => true]])
        );
    }
}
