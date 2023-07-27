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
use Elastica\Query\MultiMatch;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

class SearchTermFilter implements QueryFilterInterface
{
    protected array $fieldsToSearch;

    public function __construct(
        array $fieldsToSearch
    ) {
        $this->fieldsToSearch = $fieldsToSearch;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();

        $searchCode = $qb->query()->term(['code' => $requestConfiguration->getQueryText()]);

        $searchQuery = $qb->query()->bool();
        $searchQuery->addShould($searchCode);
        $this->addFieldsToSearchCondition($searchQuery, $requestConfiguration);

        $this->addCustomFilters($searchQuery, $requestConfiguration);

        $boolQuery->addMust($searchQuery);
    }

    protected function addFieldsToSearchCondition(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        if (0 === \count($this->fieldsToSearch)) {
            return;
        }
        $qb = new QueryBuilder();
        $nameAndDescriptionQuery = $qb->query()->multi_match();
        $nameAndDescriptionQuery->setFields($this->fieldsToSearch);
        $nameAndDescriptionQuery->setQuery($requestConfiguration->getQueryText());
        $nameAndDescriptionQuery->setType(MultiMatch::TYPE_MOST_FIELDS);
        $nameAndDescriptionQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);
        $searchQuery->addShould($nameAndDescriptionQuery);
    }

    protected function addCustomFilters(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        // Used by children classes
    }
}
