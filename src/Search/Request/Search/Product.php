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

use Elastica\Query;
use Elastica\Query\MultiMatch;
use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\AggregationBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Product implements RequestInterface
{
    private DocumentableInterface $documentable;
    private RequestConfiguration $configuration;
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductOptionRepositoryInterface $productOptionRepository;
    private ChannelContextInterface $channelContext;
    private AggregationBuilder $aggregationBuilder;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository,
        ChannelContextInterface $channelContext,
        AggregationBuilder $aggregationBuilder
    ) {
        //TODO check if exist, return a dummy documentable if not
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->channelContext = $channelContext;
        $this->aggregationBuilder = $aggregationBuilder;
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
        $currentChannelFilter = new Query\Term();
        $currentChannelFilter->setTerm('channels.code', $this->channelContext->getChannel()->getCode());
        $channelFilter = new Query\Nested();
        $channelFilter->setPath('channels');
        $channelFilter->setQuery($currentChannelFilter);

        $searchCode = new Query\Terms('code', [$this->configuration->getQueryText()]);

        $nameAndDescriptionQuery = new MultiMatch();
        $nameAndDescriptionQuery->setFields([
            'name^5', // todo configuration
            'description', // move to should ? score impact but not include in result
        ]);
        $nameAndDescriptionQuery->setQuery($this->configuration->getQueryText());
        $nameAndDescriptionQuery->setType(MultiMatch::TYPE_MOST_FIELDS);
        $nameAndDescriptionQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

        $searchQuery = new Query\BoolQuery();
        $searchQuery
            ->addShould($searchCode)
            ->addShould($nameAndDescriptionQuery)
        ;

        $this->addAttributesQueries($searchQuery);

        $bool = new Query\BoolQuery();
        $bool->addFilter($enableFilter);
        $bool->addFilter($channelFilter);
        $bool->addMust($searchQuery);

        $esQuery = Query::create($bool);
        $boolFilter = new Query\BoolQuery();
        foreach ($this->getFilters() as $filter) {
            $boolFilter->addMust($filter);
        }
        $esQuery->setPostFilter($boolFilter);
        $this->addAggregations($esQuery);

        // Manage sorting
        foreach ($this->configuration->getSorting() as $field => $order) {
            $sort = $this->getSort($field, $order);
            if (0 !== \count($sort)) {
                $esQuery->addSort($this->getSort($field, $order));
            }
        }

        dump(json_encode($esQuery->toArray(), 1));

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
            $attributeValueQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

            $attributeQuery = new Query\Nested();
            $attributeQuery->setPath(sprintf('attributes.%s', $productAttribute->getCode()))->setQuery($attributeValueQuery);

            $attributesQuery = new Query\Nested();
            $attributesQuery->setPath('attributes')->setQuery($attributeQuery);

            $searchQuery->addShould($attributeQuery);
        }
    }

    private function addAggregations(Query $query): void
    {
        $newAggs = $this->aggregationBuilder->buildAggregations(
            [
                $this->productAttributeRepository->findIsSearchableOrFilterable(),
                $this->productOptionRepository->findIsSearchableOrFilterable(),
                'main_taxon',
                'price',
            ],
            $this->getFilters()
        );

        foreach ($newAggs as $aggregation) {
            $query->addAggregation($aggregation);
        }
    }

    private function getFilters(): array
    {
        $filters = [];

        $qb = new \Elastica\QueryBuilder();

        foreach ($this->configuration->getAppliedFilters('taxon') as $field => $values) {
            $mainTaxonQuery = $qb->query()
                ->bool()
            ;
            foreach ($values as $value) {
                $mainTaxonQuery->addShould(
                    $qb->query()
                        ->term()
                        ->setTerm(sprintf('%s.code', $field), SlugHelper::toLabel($value))
                );
            }
            $filters['main_taxons'] = $qb->query()
                ->nested()
                ->setPath($field)
                ->setQuery(
                    $mainTaxonQuery
                )
            ;
        }

        $priceValue = $this->configuration->getAppliedFilters('price');
        if (0 !== \count($priceValue)) {
            $qb = new \Elastica\QueryBuilder();

            // channel filter
            $channelPriceFilter = $qb->query()
                ->term(['prices.channel_code' => $this->channelContext->getChannel()->getCode()])
            ;
            $conditions = [];
            if (\array_key_exists('min', $priceValue)) {
                $conditions['gte'] = $priceValue['min'] * 100;
            }
            if (\array_key_exists('max', $priceValue)) {
                $conditions['lte'] = $priceValue['max'] * 100;
            }
            $priceQuery = $qb->query()
                ->range('prices.price', $conditions)
            ;
            $filters['price'] = $qb->query()
                ->nested()
                ->setPath('prices')
                ->setQuery(
                    $qb->query()->bool()
                        ->addMust($channelPriceFilter)
                        ->addMust($priceQuery)
                )
            ;
        }

        foreach ($this->configuration->getAppliedFilters('attributes') as $field => $values) {
            $attributeValueQuery = new Query\BoolQuery();

            foreach ($values as $value) {
                $termQuery = new Query\Terms(sprintf('attributes.%s.value.keyword', $field), [SlugHelper::toLabel($value)]);
                $attributeValueQuery->addShould($termQuery); // todo configure the "and" or "or"
            }

            $attributeQuery = new Query\Nested();
            $attributeQuery->setPath(sprintf('attributes.%s', $field))->setQuery($attributeValueQuery);

            $filters['attributes.' . $field] = $attributeQuery;
        }

        foreach ($this->configuration->getAppliedFilters('options') as $field => $values) {
            $attributeValueQuery = new Query\BoolQuery();

            foreach ($values as $value) {
                $termQuery = new Query\Terms(sprintf('variants.options.%s.value.keyword', $field), [SlugHelper::toLabel($value)]);
                $attributeValueQuery->addShould($termQuery); // todo configure the "and" or "or"
            }

            $attributeQuery = new Query\Nested();
            $attributeQuery->setPath(sprintf('variants.options.%s', $field))->setQuery($attributeValueQuery);

            $filters['options.' . $field] = $attributeQuery;
        }

        return $filters;
    }

    // todo find solution to get more extendable
    private function getSort(string $field, string $order)
    {
        $fieldName = $field;
        if ('name' == $field) {
            $fieldName = $field . '.keyword';
        }

        switch ($field) {
            case 'name':
            case 'created_at':
                return $this->buildSort($fieldName, $order);
            case 'price':
                return self::buildSort(
                    'prices.price',
                    $order,
                    'prices',
                    'prices.channel_code', $this->channelContext->getChannel()->getCode()
                );
            case 'position':
            default:
                // Dummy value to have null sorting in ES and keep ES results sorting
                return $this->buildSort('_score', 'desc');
        }
    }

    private function buildSort(
        string $field,
        string $order,
        ?string $nestedPath = null,
        ?string $sortFilterField = null,
        ?string $sortFilterValue = null
    ): array {
        $sort = [$field => ['order' => $order]];
        if (null !== $nestedPath) {
            $sort[$field]['nested_path'] = $nestedPath;
            $sort[$field]['nested_filter'] = [
                'term' => [
                    $sortFilterField => $sortFilterValue,
                ],
            ];
        }

        return $sort;
    }
}
