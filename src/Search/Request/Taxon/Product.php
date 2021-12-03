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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Taxon;

use Elastica\Query;
use Elastica\QueryBuilder;
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
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductOptionRepositoryInterface $productOptionRepository;
    private ChannelContextInterface $channelContext;
    private AggregationBuilder $aggregationBuilder;
    private ?RequestConfiguration $configuration;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository,
        ChannelContextInterface $channelContext,
        AggregationBuilder $aggregationBuilder
    ) {
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->channelContext = $channelContext;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    public function getType(): string
    {
        return RequestInterface::TAXON_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentable;
    }

    public function getQuery(): Query
    {
        $qb = new QueryBuilder();
        $searchQuery = $qb->query()->nested()
            ->setPath('product_taxons')
            ->setQuery(
                $qb->query()->nested()
                ->setPath('product_taxons.taxon')
                ->setQuery(
                    $qb->query()->term(['product_taxons.taxon.code' => ['value' => $this->configuration->getTaxon()->getCode()]])
                )
            )
        ;

        if ($this->configuration->getTaxon()->isRoot()) {
            $searchQuery = $qb->query()->bool();
        }

        $boolQuery = $qb->query()->bool()
            ->addFilter($qb->query()->term(['enabled' => ['value' => true]]))
            ->addFilter(
                $qb->query()->nested()
                    ->setPath('channels')
                    ->setQuery(
                        $qb->query()->term(['channels.code' => ['value' => $this->channelContext->getChannel()->getCode()]])
                    )
            )
            ->addMust($searchQuery)
        ;

        $query = Query::create($boolQuery);

        $postFilter = new Query\BoolQuery();
        foreach ($this->getFilters() as $filter) {
            $postFilter->addMust($filter);
        }
        $query->setPostFilter($postFilter);
        $this->addAggregations($query);

        // Manage sorting
        $sorts = $this->configuration->getSorting() ?: ['position' => 'desc'];
        foreach ($sorts as $field => $order) {
            $sort = $this->getSort($field, $order);
            if (0 !== \count($sort)) {
                $query->addSort($sort);
            }
        }

        dump(json_encode($query->toArray(), 1));

        return $query;
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return RequestInterface::TAXON_TYPE === $type && $documentableCode === $this->getDocumentable()->getIndexCode();
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
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

        $taxonsSelected = $this->configuration->getAppliedFilters('taxons');
        if (0 !== \count($taxonsSelected)) {
            $taxonQuery = $qb->query()
                ->bool()
            ;
            foreach ($this->configuration->getAppliedFilters('taxons') as $value) {
                $taxonQuery->addShould(
                    $qb->query()
                        ->term()
                        ->setTerm('product_taxons.taxon.code', SlugHelper::toLabel($value))
                );
            }

            $filters['taxons'] = $qb->query()
                ->nested()
                ->setPath('product_taxons')
                ->setQuery(
                    $qb->query()->nested()
                        ->setPath('product_taxons.taxon')
                        ->setQuery($taxonQuery)
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

//        dump($filters);die;

        return $filters;
    }

    private function addAggregations(Query $query): void
    {
        $newAggs = $this->aggregationBuilder->buildAggregations(
            [
                $this->productAttributeRepository->findIsSearchableOrFilterable(),
                $this->productOptionRepository->findIsSearchableOrFilterable(),
                ['taxons' => $this->configuration->getTaxon()],
                'price',
            ],
            $this->getFilters()
        );

        foreach ($newAggs as $aggregation) {
            $query->addAggregation($aggregation);
        }
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
                $qb = new QueryBuilder();
                $filter = $qb->query()->nested()
                    ->setPath('product_taxons.taxon')
                    ->setQuery(
                        $qb->query()->term(['product_taxons.taxon.code' => ['value' => $this->configuration->getTaxon()->getCode()]])
                    )
                ;

                return $this->buildSort('product_taxons.position', 'asc', 'product_taxons', null, $filter);
        }
    }

    /**
     * @param string|Query\AbstractQuery|null $sortFilterValue
     */
    private function buildSort(
        string $field,
        string $order,
        ?string $nestedPath = null,
        ?string $sortFilterField = null,
        $sortFilterValue = null
    ): array {
        $sort = [$field => ['order' => $order]];
        if (null !== $nestedPath) {
            $sort[$field]['nested']['path'] = $nestedPath;
            if ($sortFilterValue instanceof Query\AbstractQuery) {
                $filter = $sortFilterValue->toArray();
            } else {
                $filter = [
                    'term' => [
                        $sortFilterField => $sortFilterValue,
                    ],
                ];
            }
            $sort[$field]['nested']['filter'] = $filter;
        }

        return $sort;
    }
}
