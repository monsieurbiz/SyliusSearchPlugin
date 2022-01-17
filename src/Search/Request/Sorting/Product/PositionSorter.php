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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product;

use Elastica\Query;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterBuilderTrait;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;

final class PositionSorter implements SorterInterface
{
    use SorterBuilderTrait;

    public function apply(Query $query, RequestConfiguration $requestConfiguration): void
    {
        $sorting = $requestConfiguration->getSorting();
        if (!\array_key_exists('position', $sorting) && 0 !== \count($sorting)) {
            return;
        }

        $query->addSort($this->buildSort('_score', 'desc'));
        if (RequestInterface::TAXON_TYPE == $requestConfiguration->getType()) {
            $qb = new QueryBuilder();
            $filter = $qb->query()->nested()
                ->setPath('product_taxons.taxon')
                ->setQuery(
                    $qb->query()->term(['product_taxons.taxon.code' => ['value' => $requestConfiguration->getTaxon()->getCode()]])
                )
            ;
            $query->addSort($this->buildSort('product_taxons.position', 'asc', 'product_taxons', null, $filter));
        }
    }
}
