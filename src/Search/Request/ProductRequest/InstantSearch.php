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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreRegistryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterRegistryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use RuntimeException;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class InstantSearch implements RequestInterface
{
    private DocumentableInterface $documentable;

    private ?RequestConfiguration $configuration;

    private QueryFilterRegistryInterface $queryFilterRegistry;

    private FunctionScoreRegistryInterface $functionScoreRegistry;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        QueryFilterRegistryInterface $queryFilterRegistry,
        FunctionScoreRegistryInterface $functionScoreRegistry
    ) {
        /** @var DocumentableInterface $documentable */
        $documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->documentable = $documentable;
        $this->queryFilterRegistry = $queryFilterRegistry;
        $this->functionScoreRegistry = $functionScoreRegistry;
    }

    public function getType(): string
    {
        return RequestInterface::INSTANT_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentable;
    }

    public function getQuery(): Query
    {
        if (null === $this->configuration) {
            throw new RuntimeException('missing configuration');
        }

        $qb = new QueryBuilder();
        $boolQuery = $qb->query()->bool();
        foreach ($this->queryFilterRegistry->all() as $queryFilter) {
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
        foreach ($this->functionScoreRegistry->all() as $functionScoreClass) {
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
