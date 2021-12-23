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

namespace MonsieurBiz\SyliusSearchPlugin\Search;

use Elastica\ResultSet;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

class Response implements ResponseInterface
{
    private RequestConfiguration $requestConfiguration;
    private AdapterInterface $adapter;
    private DocumentableInterface $documentable;
    private ?Pagerfanta $paginator = null;
    private array $filters = [];
    private iterable $filterBuilders;

    public function __construct(
        RequestConfiguration $requestConfiguration,
        AdapterInterface $adapter,
        DocumentableInterface $documentable,
        iterable $filterBuilders
    ) {
        $this->requestConfiguration = $requestConfiguration;
        $this->adapter = $adapter;
        $this->documentable = $documentable;
        $this->filterBuilders = $filterBuilders;
        $this->buildFilters();
    }

    public function getIterator()
    {
        return $this->getPaginator();
    }

    public function count()
    {
        return $this->getPaginator()->getNbResults();
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getPaginator(): Pagerfanta
    {
        if (null === $this->paginator) {
            $this->paginator = new Pagerfanta($this->adapter);
            $this->paginator->setCurrentPage($this->requestConfiguration->getPage());
            $this->paginator->setMaxPerPage($this->requestConfiguration->getLimit());
        }

        return $this->paginator;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentable;
    }

    private function buildFilters(): void
    {
        /** @var ResultSet $results */
        $results = $this->getPaginator()->getCurrentPageResults();
        $aggregations = $results->getAggregations();
        // No aggregation so don't perform filters
        if (0 === \count($aggregations)) {
            return;
        }

        array_map(function($aggregationCode, $aggregationData): void {
            foreach ($this->filterBuilders as $filterBuilder) {
                if (null !== $filter = $filterBuilder->build($this->getDocumentable(), $this->requestConfiguration, $aggregationCode, $aggregationData)) {
                    $this->filters[$filterBuilder->getPosition()] = $filter;
                }
            }
        }, array_keys($aggregations), $aggregations);

        $result = [];
        ksort($this->filters);
        foreach ($this->filters as $filters) {
            foreach ($filters as $filter) {
                $result[] = $filter;
            }
        }
        $this->filters = $result;
    }
}
