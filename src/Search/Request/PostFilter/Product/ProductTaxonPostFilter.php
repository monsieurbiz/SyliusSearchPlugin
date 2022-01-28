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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class ProductTaxonPostFilter implements PostFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        $taxonsSelected = $requestConfiguration->getAppliedFilters('taxons');
        if (0 !== \count($taxonsSelected)) {
            $taxonQuery = $qb->query()
                ->bool()
            ;
            foreach ($taxonsSelected as $value) {
                $taxonQuery->addShould(
                    $qb->query()
                        ->term()
                        ->setTerm('product_taxons.taxon.code', SlugHelper::toLabel($value))
                );
            }

            $boolQuery->addMust(
                $qb->query()
                    ->nested()
                    ->setPath('product_taxons')
                    ->setQuery(
                        $qb->query()->nested()
                            ->setPath('product_taxons.taxon')
                            ->setQuery($taxonQuery)
                    )
            );
        }
    }
}
