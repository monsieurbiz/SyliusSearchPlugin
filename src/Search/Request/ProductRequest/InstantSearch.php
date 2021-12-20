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

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        QueryFilterRegistryInterface $queryFilterRegistry
    ) {
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->queryFilterRegistry = $queryFilterRegistry;
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
        if (null === $this->configuration || '' === $this->configuration->getQueryText()) {
            throw new RuntimeException('missing query text');
        }

        $qb = new QueryBuilder();
        $boolQuery = $qb->query()->bool();
        foreach ($this->queryFilterRegistry->all() as $queryFilter) {
            $queryFilter->apply($boolQuery, $this->configuration);
        }

        return Query::create($boolQuery);
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