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

    protected array $nestedFieldsToSearch;

    public function __construct(
        array $fieldsToSearch,
        array $nestedFieldsToSearch = []
    ) {
        $this->fieldsToSearch = $fieldsToSearch;
        $this->nestedFieldsToSearch = $nestedFieldsToSearch;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();

        $searchCode = $qb->query()->term(['code' => $requestConfiguration->getQueryText()]);

        $searchQuery = $qb->query()->bool();
        $searchQuery->addShould($searchCode);
        $this->addFieldsToSearchCondition($searchQuery, $requestConfiguration);
        $this->addNestedFieldsToSearchCondition($searchQuery, $requestConfiguration);

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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function addNestedFieldsToSearchCondition(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        if (0 === \count($this->nestedFieldsToSearch)) {
            return;
        }

        $nestedFields = [];

        // Group nested fields by nested path
        foreach ($this->nestedFieldsToSearch as $nestedFieldToSearch) {
            $nestedFieldExpression = explode(':', $nestedFieldToSearch);
            if (2 !== \count($nestedFieldExpression)) {
                continue;
            }

            $nestedFields[$nestedFieldExpression[0]][] = str_replace(':', '.', $nestedFieldToSearch);
        }

        // Create queries by nested path and nested values
        $qb = new QueryBuilder();
        foreach ($nestedFields as $nestedField => $nestedFieldExpressions) {
            $nestedFieldValueQuery = $qb->query()->multi_match();
            $nestedFieldValueQuery->setFields($nestedFieldExpressions);
            $nestedFieldValueQuery->setQuery($requestConfiguration->getQueryText());
            $nestedFieldValueQuery->setType(MultiMatch::TYPE_MOST_FIELDS);
            $nestedFieldValueQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

            $nestedFieldQuery = $qb->query()->nested();
            $nestedFieldQuery->setPath($nestedField)->setQuery($nestedFieldValueQuery);

            $searchQuery->addShould($nestedFieldQuery);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addCustomFilters(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        // Used by children classes
    }
}
