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
use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\RequestInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Product implements RequestInterface
{
    private DocumentableInterface $documentable;

    private RequestConfiguration $configuration;
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

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getQuery(): Query
    {
        if ('' === $this->configuration->getQueryText()) {
            throw new \Exception('missing query text'); //todo
        }

        $enableFilter = new Query\Terms('enabled', [true]);
        // todo add channel filter

        $searchCode = new Query\Terms('code', [$this->configuration->getQueryText()]);

        $nameAndDescriptionQuery = new MultiMatch();
        $nameAndDescriptionQuery->setFields([
            'name^5', // todo configuration
            'description', // move to should ? score impact but not include in result
        ]);
        $nameAndDescriptionQuery->setQuery($this->configuration->getQueryText());
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
        $this->addFilters($esQuery);
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
            $attributeValueQuery->setQuery($this->configuration->getQueryText());

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

            $attributeCodesAgg = new Terms('names');
            $attributeCodesAgg->setField(sprintf('attributes.%s.name', $productAttribute->getCode()));
            $attributeCodesAgg->addAggregation($attributeValuesAgg);

            $attributeAgg = new Nested($productAttribute->getCode(), sprintf('attributes.%s', $productAttribute->getCode()));
            $attributeAgg->addAggregation($attributeCodesAgg);

            $attributesAgg = new Nested('attributes', 'attributes');
            $attributesAgg->addAggregation($attributeAgg);

            $query->addAggregation($attributesAgg);
        }
    }

    private function addFilters(Query $query): void
    {
        $bool = new Query\BoolQuery();
        foreach ($this->configuration->getAppliedFilters() as $field => $values) {
            $attributeValueQuery = new Query\BoolQuery();

            foreach ($values as $value) {
                $termQuery = new Query\Terms(sprintf('attributes.%s.value.keyword', $field), [SlugHelper::toLabel($value)]);
                $attributeValueQuery->addShould($termQuery); // todo configure the "and" or "or"
            }

            $attributeQuery = new Query\Nested();
            $attributeQuery->setPath(sprintf('attributes.%s', $field))->setQuery($attributeValueQuery);

            $attributesQuery = new Query\Nested();
            $attributesQuery->setPath('attributes')->setQuery($attributeQuery);

            $bool->addFilter($attributesQuery);
        }

        $query->setPostFilter($bool);
    }
}
