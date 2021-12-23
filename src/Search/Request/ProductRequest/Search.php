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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\ProductRequest;

use Elastica\Query;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\AggregationBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterRegistryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterRegistryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterRegistryInterface;
use RuntimeException;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class Search implements RequestInterface
{
    private DocumentableInterface $documentable;
    private RequestConfiguration $configuration;
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductOptionRepositoryInterface $productOptionRepository;
    private AggregationBuilder $aggregationBuilder;
    private QueryFilterRegistryInterface $queryFilterRegistry;
    private PostFilterRegistryInterface $postFilterRegistry;
    private SorterRegistryInterface $sorterRegistry;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository,
        AggregationBuilder $aggregationBuilder,
        QueryFilterRegistryInterface $queryFilterRegistry,
        PostFilterRegistryInterface $postFilterRegistry,
        SorterRegistryInterface $sorterRegistry
    ) {
        //TODO check if exist, return a dummy documentable if not
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryFilterRegistry = $queryFilterRegistry;
        $this->postFilterRegistry = $postFilterRegistry;
        $this->sorterRegistry = $sorterRegistry;
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
            throw new RuntimeException('missing query text');
        }

        $qb = new QueryBuilder();

        $boolQuery = $qb->query()->bool();
        foreach ($this->queryFilterRegistry->all() as $queryFilter) {
            $queryFilter->apply($boolQuery, $this->configuration);
        }

        $query = Query::create($boolQuery);
        $postFilter = new Query\BoolQuery();
        foreach ($this->postFilterRegistry->all() as $postFilterApplier) {
            $postFilterApplier->apply($postFilter, $this->configuration);
        }
        $query->setPostFilter($postFilter);

        $this->addAggregations($query, $postFilter);

        foreach ($this->sorterRegistry->all() as $sorter) {
            $sorter->apply($query, $this->configuration);
        }

        dump(json_encode($query->toArray(), 1));

        return $query;
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return $type == $this->getType() && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    private function addAggregations(Query $query, Query\BoolQuery $postFilter): void
    {
        $aggregations = $this->aggregationBuilder->buildAggregations(
            [
                'main_taxon',
                'price',
                $this->productAttributeRepository->findIsSearchableOrFilterable(),
                $this->productOptionRepository->findIsSearchableOrFilterable(),
            ],
            $postFilter->hasParam('must') ? $postFilter->getParam('must') : []
        );

        foreach ($aggregations as $aggregation) {
            $query->addAggregation($aggregation);
        }
    }
}
