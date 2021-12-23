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
use Elastica\Query\MultiMatch;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class SearchTermFilter implements QueryFilterInterface
{
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductOptionRepositoryInterface $productOptionRepository;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();

        $searchCode = $qb->query()->term(['code' => $requestConfiguration->getQueryText()]);

        $nameAndDescriptionQuery = $qb->query()->multi_match();
        $nameAndDescriptionQuery->setFields([
            'name^5', // todo configuration
            'description', // move to should ? score impact but not include in result
        ]);
        $nameAndDescriptionQuery->setQuery($requestConfiguration->getQueryText());
        $nameAndDescriptionQuery->setType(MultiMatch::TYPE_MOST_FIELDS);
        $nameAndDescriptionQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

        $searchQuery = $qb->query()->bool();
        $searchQuery
            ->addShould($searchCode)
            ->addShould($nameAndDescriptionQuery)
        ;

        $this->addAttributesQueries($searchQuery, $requestConfiguration);
        $this->addOptionsQueries($searchQuery, $requestConfiguration);

        $boolQuery->addMust($searchQuery);
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
                sprintf('variants.options.%s.value^%d', $productOption->getCode(), $productOption->getSearchWeight()),
            ]);
            $attributeValueQuery->setQuery($requestConfiguration->getQueryText());
            $attributeValueQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

            $attributeQuery = $qb->query()->nested();
            $attributeQuery->setPath(sprintf('variants.options.%s', $productOption->getCode()))->setQuery($attributeValueQuery);

            $searchQuery->addShould($attributeQuery);
        }
    }
}
