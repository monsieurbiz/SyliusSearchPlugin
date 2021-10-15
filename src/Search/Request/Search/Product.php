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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Search;

use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Query\MultiMatch;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\RequestInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Product implements RequestInterface
{
    private DocumentableInterface $documentable;

    private array $queryParameters = [];
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        //TODO check if exist, return a dummy documentable if not
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->productAttributeRepository = $productAttributeRepository;
    }

    public function getType(): string
    {
        return RequestInterface::SEARCH_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentable;
    }

    public function setQueryParameters(array $parameters): void
    {
        $this->queryParameters = $parameters;
    }

    public function getQuery(): Query
    {
        if (!\array_key_exists('query_text', $this->queryParameters)) {
            throw new \Exception('missing query text'); //todo
        }

        $enableFilter = new Query\Terms('enabled', [true]);
        // todo add channel filter

        $searchCode = new Query\Terms('code', [$this->queryParameters['query_text']]);

        $nameAndDescriptionQuery = new MultiMatch();
        $nameAndDescriptionQuery->setFields([
            'name^5', // todo configuration
            'description', // move to should ? score impact but not include in result
        ]);
        $nameAndDescriptionQuery->setQuery($this->queryParameters['query_text']);
        $nameAndDescriptionQuery->setType(MultiMatch::TYPE_MOST_FIELDS);

        $searchQuery = new Query\BoolQuery();
        $searchQuery
            ->addShould($searchCode)
            ->addShould($nameAndDescriptionQuery)
        ;

        $this->addAttributesQueries($searchQuery);

        $bool = new Query\BoolQuery();
        $bool->addFilter($enableFilter);
        $bool->addMust($searchQuery);

        $esQuery = Query::create($bool);
        $this->addAggregations($esQuery);

        return $esQuery;
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return $type == $this->getType() && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    private function addAttributesQueries(Query\BoolQuery $searchQuery): void
    {
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            if (!$productAttribute->isSearchable()) {
                continue;
            }

            $attributeValueQuery = new MultiMatch();
            $attributeValueQuery->setFields([
                sprintf('attributes.%s.value^%d', $productAttribute->getCode(), $productAttribute->getSearchWeight()),
            ]);
            $attributeValueQuery->setQuery($this->queryParameters['query_text']);

            $attributeQuery = new Query\Nested();
            $attributeQuery->setPath(sprintf('attributes.%s', $productAttribute->getCode()))->setQuery($attributeValueQuery);

            $attributesQuery = new Query\Nested();
            $attributesQuery->setPath('attributes')->setQuery($attributeQuery);

            $searchQuery->addShould($attributeQuery);
        }
    }

    private function addAggregations(Query $query): void
    {
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            if (!$productAttribute->isFilterable()) {
                continue;
            }
            $attributeValuesAgg = new Terms('values');
            $attributeValuesAgg->setField(sprintf('attributes.%s.value.keyword', $productAttribute->getCode()));

            $attributeAgg = new Nested($productAttribute->getCode(), sprintf('attributes.%s', $productAttribute->getCode()));
            $attributeAgg->addAggregation($attributeValuesAgg);

            $attributesAgg = new Nested('attributes', 'attributes');
            $attributesAgg->addAggregation($attributeAgg);

            $query->addAggregation($attributesAgg);
        }
    }
}
