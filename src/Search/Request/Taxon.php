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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use Elastica\Query;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;
use RuntimeException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Taxon implements TaxonInterface
{
    protected ServiceRegistryInterface $documentableRegistry;

    protected ChannelContextInterface $channelContext;

    protected string $documentType;

    protected AggregationBuilder $aggregationBuilder;

    protected ?RequestConfiguration $configuration;

    /**
     * @var iterable<QueryFilterInterface>
     */
    protected iterable $queryFilters;

    /**
     * @var iterable<PostFilterInterface>
     */
    protected iterable $postFilters;

    /**
     * @var iterable<SorterInterface>
     */
    protected iterable $sorters;

    /**
     * @var iterable<FunctionScoreInterface>
     */
    protected iterable $functionScores;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ChannelContextInterface $channelContext,
        AggregationBuilder $aggregationBuilder,
        string $documentType,
        iterable $queryFilters,
        iterable $postFilters,
        iterable $sorters,
        iterable $functionScores
    ) {
        $this->documentableRegistry = $documentableRegistry;
        $this->channelContext = $channelContext;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->documentType = $documentType;
        $this->queryFilters = $queryFilters;
        $this->postFilters = $postFilters;
        $this->sorters = $sorters;
        $this->functionScores = $functionScores;
    }

    public function getType(): string
    {
        return RequestInterface::TAXON_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        /** @phpstan-ignore-next-line  */
        return $this->documentableRegistry->get('search.documentable.' . $this->documentType);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getQuery(): Query
    {
        if (!($configuration = $this->configuration)) {
            throw new RuntimeException('Configuration is not set');
        }

        $qb = new QueryBuilder();

        $boolQuery = $qb->query()->bool();
        foreach ($this->queryFilters as $queryFilter) {
            $queryFilter->apply($boolQuery, $configuration);
        }

        $query = Query::create($boolQuery);
        $postFilter = new Query\BoolQuery();
        foreach ($this->postFilters as $postFilterApplier) {
            $postFilterApplier->apply($postFilter, $configuration);
        }
        $query->setPostFilter($postFilter);

        $this->addAggregations($query, $postFilter);

        foreach ($this->sorters as $sorter) {
            $sorter->apply($query, $configuration);
        }

        /** @var Query\AbstractQuery $queryObject */
        $queryObject = $query->getQuery();
        $functionScore = $qb->query()->function_score()
            ->setQuery($queryObject)
            ->setBoostMode(Query\FunctionScore::BOOST_MODE_MULTIPLY)
            ->setScoreMode(Query\FunctionScore::SCORE_MODE_MULTIPLY)
        ;
        foreach ($this->functionScores as $functionScoreClass) {
            $functionScoreClass->addFunctionScore($functionScore, $configuration);
        }

        $query->setQuery($functionScore);

        return $query;
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return $type == $this->getType() && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addAggregations(Query $query, Query\BoolQuery $postFilter): void
    {
        // Used by children classes
    }
}
