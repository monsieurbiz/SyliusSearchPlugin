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
use Elastica\Query\MultiMatch;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\SearchTermFilter as BaseSearchTermFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class SearchTermFilter extends BaseSearchTermFilter
{
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    private ProductOptionRepositoryInterface $productOptionRepository;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository,
        array $fieldsToSearch
    ) {
        parent::__construct($fieldsToSearch);
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
    }

    protected function addCustomFilters(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        $this->addAttributesQueries($searchQuery, $requestConfiguration);
        $this->addOptionsQueries($searchQuery, $requestConfiguration);
    }

    private function addAttributesQueries(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            if (!$productAttribute->isSearchable()) {
                continue;
            }

            $attributeValueQuery = $qb->query()->multi_match();
            $attributeValueQuery->setFields([
                sprintf('attributes.%s.value^%d', $productAttribute->getCode(), $productAttribute->getSearchWeight()),
            ]);
            $attributeValueQuery->setQuery($requestConfiguration->getQueryText());
            $attributeValueQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

            $attributeQuery = $qb->query()->nested();
            $attributeQuery->setPath(sprintf('attributes.%s', $productAttribute->getCode()))->setQuery($attributeValueQuery);

            $searchQuery->addShould($attributeQuery);
        }
    }

    private function addOptionsQueries(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        foreach ($this->productOptionRepository->findIsSearchableOrFilterable() as $productOption) {
            if (!$productOption->isSearchable()) {
                continue;
            }

            $attributeValueQuery = $qb->query()->multi_match();
            $attributeValueQuery->setFields([
                sprintf('options.%s.values.value^%d', $productOption->getCode(), $productOption->getSearchWeight()),
            ]);
            $attributeValueQuery->setQuery($requestConfiguration->getQueryText());
            $attributeValueQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

            $attributeQuery = $qb->query()->nested();
            $attributeQuery->setPath(sprintf('options.%s.values', $productOption->getCode()))->setQuery($attributeValueQuery);

            $searchQuery->addShould($attributeQuery);
        }
    }
}
