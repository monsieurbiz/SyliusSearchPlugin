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
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\RangeFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

class Response implements ResponseInterface
{
    private RequestConfiguration $requestConfiguration;
    private AdapterInterface $adapter;
    private ?Pagerfanta $paginator = null;
    private array $filters = [];

    public function __construct(RequestConfiguration $requestConfiguration, AdapterInterface $adapter)
    {
        $this->requestConfiguration = $requestConfiguration;
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

    public function getPaginator(): Pagerfanta
    {
        if (null === $this->paginator) {
            $this->paginator = new Pagerfanta($this->adapter);
            $this->paginator->setCurrentPage($this->requestConfiguration->getPage());
            $this->paginator->setMaxPerPage($this->requestConfiguration->getLimit());
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

        // todo main taxon
        $taxonAggregation = $aggregations['main_taxon'] ?? null;
        if ($taxonAggregation && $taxonAggregation['doc_count'] > 0) {
            $filter = new Filter('main_taxon', 'monsieurbiz_searchplugin.filters.taxon_filter', $taxonAggregation['doc_count'], 'taxon');

            // Get main taxon code in aggregation
            $taxonCodeBuckets = $taxonAggregation['codes']['buckets'] ?? [];
            foreach ($taxonCodeBuckets as $taxonCodeBucket) {
                if (0 === $taxonCodeBucket['doc_count']) {
                    continue;
                }
                $taxonCode = $taxonCodeBucket['key'];
                $taxonName = null;

                // Get main taxon level in aggregation
                $taxonLevelBuckets = $taxonCodeBucket['levels']['buckets'] ?? [];
                foreach ($taxonLevelBuckets as $taxonLevelBucket) {
                    // Get main taxon name in aggregation
                    $taxonNameBuckets = $taxonLevelBucket['names']['buckets'] ?? [];
                    foreach ($taxonNameBuckets as $taxonNameBucket) {
                        $taxonName = $taxonNameBucket['key'];
                        $filter->addValue($taxonName ?? $taxonCode, $taxonCodeBucket['doc_count']);
                        break 2;
                    }
                }
            }

            // Put taxon filter in first if contains value
            if (0 !== \count($filter->getValues())) {
                $this->filters[] = $filter;
            }
        }

        // todo price
        $priceAggregation = $aggregations['prices']['prices'] ?? null;
        if ($priceAggregation && $priceAggregation['doc_count'] > 0) {
            $this->filters[] = new RangeFilter(
                'price',
                'monsieurbiz_searchplugin.filters.price_filter',
                'monsieurbiz_searchplugin.filters.price_min',
                'monsieurbiz_searchplugin.filters.price_max',
                (int) floor(($priceAggregation['prices_stats']['min'] ?? 0) / 100),
                (int) ceil(($priceAggregation['prices_stats']['max'] ?? 0) / 100)
            );
        }

        // Retrieve filters in aggregations
        foreach (['attributes', 'options'] as $aggregationType) {
            $attributeAggregations = $aggregations[$aggregationType] ?? [];
            $attributeAggregations = $attributeAggregations[$aggregationType] ?? $attributeAggregations;
            unset($attributeAggregations['doc_count']);
            foreach ($attributeAggregations as $attributeCode => $attributeAggregation) {
                if (isset($attributeAggregation[$attributeCode])) {
                    $attributeAggregation = $attributeAggregation[$attributeCode];
                }
                $attributeNameBuckets = $attributeAggregation['names']['buckets'] ?? [];
                foreach ($attributeNameBuckets as $attributeNameBucket) {
                    $attributeValueBuckets = $attributeNameBucket['values']['buckets'] ?? [];
                    $filter = new Filter($attributeCode, $attributeNameBucket['key'], $attributeNameBucket['doc_count'], $aggregationType);
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
}
