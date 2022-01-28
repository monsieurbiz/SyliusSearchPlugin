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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class TaxonFilter implements QueryFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        $searchQuery = $qb->query()->nested()
            ->setPath('product_taxons')
            ->setQuery(
                $qb->query()->nested()
                    ->setPath('product_taxons.taxon')
                    ->setQuery(
                        $qb->query()->term(['product_taxons.taxon.code' => ['value' => $requestConfiguration->getTaxon()->getCode()]])
                    )
            )
        ;
        if ($requestConfiguration->getTaxon()->isRoot()) {
            $searchQuery = $qb->query()->bool();
        }

        $boolQuery->addMust($searchQuery);
    }
}
