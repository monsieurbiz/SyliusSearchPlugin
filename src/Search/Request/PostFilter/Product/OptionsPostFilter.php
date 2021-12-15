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

final class OptionsPostFilter implements PostFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        foreach ($requestConfiguration->getAppliedFilters('options') as $field => $values) {
            $optionValueQuery = $qb->query()->bool();

            foreach ($values as $value) {
                $termQuery = $qb->query()->term([sprintf('variants.options.%s.value.keyword', $field) => SlugHelper::toLabel($value)]);
                $optionValueQuery->addShould($termQuery); // todo configure the "and" or "or"
            }

            $optionQuery = $qb->query()->nested();
            $optionQuery->setPath(sprintf('variants.options.%s', $field))->setQuery($optionValueQuery);

            $boolQuery->addMust($optionQuery);
        }
    }
}
