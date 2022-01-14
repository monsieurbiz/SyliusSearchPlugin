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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

class IsInStockFilter implements QueryFilterInterface
{
    private bool $enableStockFilter;

    public function __construct(bool $enableStockFilter)
    {
        $this->enableStockFilter = $enableStockFilter;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        if (!$this->enableStockFilter) {
            return;
        }

        $qb = new QueryBuilder();
        $boolQuery->addFilter(
            $qb->query()->nested()
                ->setPath('variants')
                ->setQuery(
                    $qb->query()->term(['variants.is_in_stock' => ['value' => true]])
                )
        );
    }
}
