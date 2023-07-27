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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use RuntimeException;
use Sylius\Component\Registry\ServiceRegistryInterface;

class InstantSearch implements InstantSearchInterface
{
    protected ServiceRegistryInterface $documentableRegistry;

    protected ?RequestConfiguration $configuration;

    protected string $documentType;

    /**
     * @var iterable<QueryFilterInterface>
     */
    protected iterable $queryFilters;

    /**
     * @var iterable<FunctionScoreInterface>
     */
    protected iterable $functionScores;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        string $documentType,
        iterable $queryFilters,
        iterable $functionScores
    ) {
        $this->documentableRegistry = $documentableRegistry;
        $this->documentType = $documentType;
        $this->queryFilters = $queryFilters;
        $this->functionScores = $functionScores;
    }

    public function getType(): string
    {
        return RequestInterface::INSTANT_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        /** @phpstan-ignore-next-line  */
        return $this->documentableRegistry->get('search.documentable.' . $this->documentType);
    }

    public function getQuery(): Query
    {
        if (null === $this->configuration) {
            throw new RuntimeException('missing configuration');
        }

        $qb = new QueryBuilder();
        $boolQuery = $qb->query()->bool();
        foreach ($this->queryFilters as $queryFilter) {
            $queryFilter->apply($boolQuery, $this->configuration);
        }

        $query = Query::create($boolQuery);

        /** @var Query\AbstractQuery $queryObject */
        $queryObject = $query->getQuery();
        $functionScore = $qb->query()->function_score()
            ->setQuery($queryObject)
            ->setBoostMode(Query\FunctionScore::BOOST_MODE_MULTIPLY)
            ->setScoreMode(Query\FunctionScore::SCORE_MODE_MULTIPLY)
        ;
        foreach ($this->functionScores as $functionScoreClass) {
            $functionScoreClass->addFunctionScore($functionScore, $this->configuration);
        }

        $query->setQuery($functionScore);

        return $query;
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return RequestInterface::INSTANT_TYPE == $type && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }
}
