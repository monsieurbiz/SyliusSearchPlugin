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

use Elastica\Aggregation;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Query\MultiMatch;
use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\RequestInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Product implements RequestInterface
{
    private DocumentableInterface $documentable;

    private RequestConfiguration $configuration;
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductOptionRepositoryInterface $productOptionRepository;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository
    ) {
        //TODO check if exist, return a dummy documentable if not
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
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
        $boolFilter = $this->getFilters();
        $esQuery->setPostFilter($boolFilter);
        $this->addAggregations($esQuery);
        dump($esQuery->toArray());

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
        $attributesAgg = new Nested('attributes', 'attributes');
        $filtredAttributesAgg = new Aggregation\Filter('attributes');
        $filtredAttributesAgg->setFilter($this->getFilters(null, ['options']));
        $filtredAttributesAgg->addAggregation($attributesAgg);
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

            $boolFilter = $this->getFilters($productAttribute->getCode(), ['attributes']);
            $filter = new Aggregation\Filter($productAttribute->getCode());
            $filter->setFilter($boolFilter);
            $filter->addAggregation($attributeAgg);

            $attributesAgg->addAggregation($filter);
        }

        $optionsAgg = new Nested('options', 'variants.options');
        $filtredOptionsAgg = new Aggregation\Filter('options');
        $filtredOptionsAgg->setFilter($this->getFilters(null, ['attributes']));
        $filtredOptionsAgg->addAggregation($optionsAgg);
        foreach ($this->productOptionRepository->findIsSearchableOrFilterable() as $productOption) {
            if (!$productOption->isFilterable()) {
                continue;
            }
            $attributeValuesAgg = new Terms('values');
            $attributeValuesAgg->setField(sprintf('variants.options.%s.value.keyword', $productOption->getCode()));

            $attributeCodesAgg = new Terms('names');
            $attributeCodesAgg->setField(sprintf('variants.options.%s.name', $productOption->getCode()));
            $attributeCodesAgg->addAggregation($attributeValuesAgg);

            $attributeAgg = new Nested($productOption->getCode(), sprintf('variants.options.%s', $productOption->getCode()));
            $attributeAgg->addAggregation($attributeCodesAgg);

            $boolFilter = $this->getFilters($productOption->getCode(), ['options']);
            $filter = new Aggregation\Filter($productOption->getCode());
            $filter->setFilter($boolFilter);
            $filter->addAggregation($attributeAgg);

            $optionsAgg->addAggregation($filter);
        }

        if (0 < \count($attributesAgg->getAggs())) {
            $query->addAggregation($filtredAttributesAgg);
        }

        if (0 < \count($optionsAgg->getAggs())) {
            $query->addAggregation($filtredOptionsAgg);
        }
    }

    private function getFilters($currentAttribute = null, array $filtreTypes = ['attributes', 'options']): Query\BoolQuery
    {
        $bool = new Query\BoolQuery();
        if (\in_array('attributes', $filtreTypes, true)) {
            foreach ($this->configuration->getAppliedFilters('attributes') as $field => $values) {
                if ($currentAttribute == $field) {
                    continue;
                }
                $attributeValueQuery = new Query\BoolQuery();

                foreach ($values as $value) {
                    $termQuery = new Query\Terms(sprintf('attributes.%s.value.keyword', $field), [SlugHelper::toLabel($value)]);
                    $attributeValueQuery->addShould($termQuery); // todo configure the "and" or "or"
                }

                $attributeQuery = new Query\Nested();
                $attributeQuery->setPath(sprintf('attributes.%s', $field))->setQuery($attributeValueQuery);

                $bool->addMust($attributeQuery);
            }
        }

        if (\in_array('options', $filtreTypes, true)) {
            foreach ($this->configuration->getAppliedFilters('options') as $field => $values) {
                if ($currentAttribute == $field) {
                    continue;
                }

                $attributeValueQuery = new Query\BoolQuery();

                foreach ($values as $value) {
                    $termQuery = new Query\Terms(sprintf('variants.options.%s.value.keyword', $field), [SlugHelper::toLabel($value)]);
                    $attributeValueQuery->addShould($termQuery); // todo configure the "and" or "or"
                }

                $attributeQuery = new Query\Nested();
                $attributeQuery->setPath(sprintf('variants.options.%s', $field))->setQuery($attributeValueQuery);

                $bool->addMust($attributeQuery);
            }
        }

        return $bool;
    }
}
