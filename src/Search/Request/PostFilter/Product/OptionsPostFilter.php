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

final class OptionsPostFilter implements PostFilterInterface
{
    private bool $enableStockFilter;

    public function __construct(bool $enableStockFilter)
    {
        $this->enableStockFilter = $enableStockFilter;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        foreach ($requestConfiguration->getAppliedFilters('options') as $field => $values) {
            $optionValueQuery = $qb->query()->bool();
            foreach ($values as $value) {
                $termQuery = $qb->query()->term([\sprintf('options.%s.values.value.keyword', $field) => SlugHelper::toLabel($value)]);
                $optionValueQuery->addShould($termQuery); // todo configure the "and" or "or"
            }

            $optionQuery = $qb->query()->nested();
            $condition = $qb->query()->bool()
                ->addMust($qb->query()->term([\sprintf('options.%s.values.enabled', $field) => true]))
            ;
            if ($this->enableStockFilter) {
                $condition->addMust($qb->query()->term([\sprintf('options.%s.values.is_in_stock', $field) => true]));
            }
            $condition->addMust($optionValueQuery);
            $optionQuery->setPath(\sprintf('options.%s.values', $field))->setQuery($condition);

            $boolQuery->addMust($optionQuery);
        }
    }
}
