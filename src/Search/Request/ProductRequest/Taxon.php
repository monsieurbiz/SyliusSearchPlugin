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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\ProductRequest;

use Elastica\Query;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\AggregationBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreRegistryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;
use RuntimeException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class Taxon implements RequestInterface
{
    private DocumentableInterface $documentable;

    private ProductAttributeRepositoryInterface $productAttributeRepository;

    private ProductOptionRepositoryInterface $productOptionRepository;

    private ChannelContextInterface $channelContext;

    private AggregationBuilder $aggregationBuilder;

    private ?RequestConfiguration $configuration;

    /**
     * @var iterable<QueryFilterInterface>
     */
    private iterable $queryFilters;

    /**
     * @var iterable<PostFilterInterface>
     */
    private iterable $postFilters;

    /**
     * @var iterable<SorterInterface>
     */
    private iterable $sorters;

    private FunctionScoreRegistryInterface $functionScoreRegistry;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository,
        ChannelContextInterface $channelContext,
        AggregationBuilder $aggregationBuilder,
        iterable $queryFilters,
        iterable $postFilters,
        iterable $sorters,
        FunctionScoreRegistryInterface $functionScoreRegistry
    ) {
        /** @var DocumentableInterface $documentable */
        $documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->documentable = $documentable;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->channelContext = $channelContext;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryFilters = $queryFilters;
        $this->postFilters = $postFilters;
        $this->sorters = $sorters;
        $this->functionScoreRegistry = $functionScoreRegistry;
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

        $boolQuery = $qb->query()->bool();
        foreach ($this->queryFilters as $queryFilter) {
            $queryFilter->apply($boolQuery, $this->configuration);
        }

        $query = Query::create($boolQuery);
        $postFilter = new Query\BoolQuery();
        foreach ($this->postFilters as $postFilterApplier) {
            $postFilterApplier->apply($postFilter, $this->configuration);
        }
        $query->setPostFilter($postFilter);

        $this->addAggregations($query, $postFilter);

        foreach ($this->sorters as $sorter) {
            $sorter->apply($query, $this->configuration);
        }

        /** @var Query\AbstractQuery $queryObject */
        $queryObject = $query->getQuery();
        $functionScore = $qb->query()->function_score()
            ->setQuery($queryObject)
            ->setBoostMode(Query\FunctionScore::BOOST_MODE_MULTIPLY)
            ->setScoreMode(Query\FunctionScore::SCORE_MODE_MULTIPLY)
        ;
        foreach ($this->functionScoreRegistry->all() as $functionScoreClass) {
            $functionScoreClass->addFunctionScore($functionScore, $this->configuration);
        }

        $query->setQuery($functionScore);

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

    private function addAggregations(Query $query, Query\BoolQuery $postFilter): void
    {
        if (null === $this->configuration) {
            throw new RuntimeException('Missing request configuration');
        }
        $aggregations = $this->aggregationBuilder->buildAggregations(
            [
                ['taxons' => $this->configuration->getTaxon()],
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
