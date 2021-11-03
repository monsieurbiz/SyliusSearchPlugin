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
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\Filter;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

class Response implements ResponseInterface
{
    private AdapterInterface $adapter;
    private ?Pagerfanta $paginator = null;
    private array $filters = [];

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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

    private function getPaginator(): Pagerfanta
    {
        if (null === $this->paginator) {
            $this->paginator = new Pagerfanta($this->adapter);
        }

        return $this->paginator;
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

        // Retrieve filters in aggregations
        $attributeAggregations = $aggregations['attributes'] ?? [];
        unset($attributeAggregations['doc_count']);
        foreach ($attributeAggregations as $attributeCode => $attributeAggregation) {
            if (isset($attributeAggregation[$attributeCode])) {
                $attributeAggregation = $attributeAggregation[$attributeCode];
            }
            $attributeNameBuckets = $attributeAggregation['names']['buckets'] ?? [];
            foreach ($attributeNameBuckets as $attributeNameBucket) {
                $attributeValueBuckets = $attributeNameBucket['values']['buckets'] ?? [];
                $filter = new Filter($attributeCode, $attributeNameBucket['key'], $attributeNameBucket['doc_count']);
                foreach ($attributeValueBuckets as $attributeValueBucket) {
                    if (0 === $attributeValueBucket['doc_count']) {
                        continue;
                    }
                    if (isset($attributeValueBucket['key']) && isset($attributeValueBucket['doc_count'])) {
                        $filter->addValue($attributeValueBucket['key'], $attributeValueBucket['doc_count']);
                    }
                }
                $this->filters[] = $filter;
            }
        }
    }
}
