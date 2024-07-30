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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class MainTaxonPostFilter implements PostFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        foreach ($requestConfiguration->getAppliedFilters('taxon') as $field => $values) {
            $mainTaxonQuery = $qb->query()
                ->bool()
            ;
            $values = array_filter($values) ?? [];
            foreach ($values as $value) {
                $mainTaxonQuery->addShould(
                    $qb->query()
                        ->term()
                        ->setTerm(\sprintf('%s.code', $field), SlugHelper::toLabel($value))
                );
            }
            $boolQuery->addMust(
                $qb->query()
                    ->nested()
                    ->setPath($field)
                    ->setQuery(
                        $mainTaxonQuery
                    )
            );
        }
    }
}
