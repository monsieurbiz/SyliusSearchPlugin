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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

use Elastica\ResultSet as ElasticaResultSet;
use JoliCode\Elastically\Result;
use MonsieurBiz\SyliusSearchPlugin\Adapter\ResultSetAdapter;
use MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Core\Model\TaxonInterface;

class ResultSet
{
    /** @var Result[] */
    private $results = [];

    /** @var int */
    private $totalHits;

    /** @var int */
    private $maxItems;

    /** @var int */
    private $page;

    /** @var Filter[] */
    private $filters = [];

    /** @var RangeFilter|null */
    private $priceFilter;

    /** @var Filter|null */
    private $taxonFilter;

    /** @var Filter|null */
    private $mainTaxonFilter;

    /** @var Pagerfanta */
    private $pager;

    /**
     * SearchResults constructor.
     *
     * @param int $maxItems
     * @param int $page
     * @param ElasticaResultSet|null $resultSet
     * @param TaxonInterface|null $taxon
     */
    public function __construct(int $maxItems, int $page, ?ElasticaResultSet $resultSet = null, ?TaxonInterface $taxon = null)
    {
        $this->maxItems = $maxItems;
        $this->page = $page;

        // Empty result set
        if (null === $resultSet) {
            $this->totalHits = 0;
            $this->results = [];
            $this->filters = [];
        } else {
            /** @var Result $result */
            foreach ($resultSet as $result) {
                $this->results[] = $result->getModel();
            }
            $this->totalHits = $resultSet->getTotalHits();
            $this->initFilters($resultSet, $taxon);
        }

        $this->initPager();
    }

    /**
     * Init pager with Pager Fanta.
     */
    private function initPager(): void
    {
        $adapter = new ResultSetAdapter($this);
        $this->pager = new Pagerfanta($adapter);
        $this->pager->setMaxPerPage($this->maxItems);
        $this->pager->setCurrentPage($this->page);
    }

    /**
     * Init filters array depending on result aggregations.
     *
     * @param ElasticaResultSet $resultSet
     * @param TaxonInterface|null $taxon
     */
    private function initFilters(ElasticaResultSet $resultSet, ?TaxonInterface $taxon = null): void
    {
        $aggregations = $resultSet->getAggregations();
        // No aggregation so don't perform filters
        if (empty($aggregations)) {
            return;
        }

        // Retrieve filters labels in aggregations
        $attributes = [];
        $attributeAggregations = $aggregations['attributes'] ?? [];
        unset($attributeAggregations['doc_count']);
        $attributeCodeBuckets = $attributeAggregations['codes']['buckets'] ?? [];
        foreach ($attributeCodeBuckets as $attributeCodeBucket) {
            $attributeCode = $attributeCodeBucket['key'];
            $attributeNameBuckets = $attributeCodeBucket['names']['buckets'] ?? [];
            foreach ($attributeNameBuckets as $attributeNameBucket) {
                $attributeName = $attributeNameBucket['key'];
                $attributes[$attributeCode] = $attributeName;
                break;
            }
        }

        // Retrieve filters values in aggregations
        $filterAggregations = $aggregations['filters'] ?? [];
        unset($filterAggregations['doc_count']);
        foreach ($filterAggregations as $field => $aggregation) {
            if (0 === $aggregation['doc_count']) {
                continue;
            }
            $filter = new Filter($field, $attributes[$field] ?? $field, $aggregation['doc_count']);
            $buckets = $aggregation['values']['buckets'] ?? [];
            foreach ($buckets as $bucket) {
                if (isset($bucket['key']) && isset($bucket['doc_count'])) {
                    $filter->addValue($bucket['key'], $bucket['doc_count']);
                }
            }
            $this->filters[] = $filter;
        }
        $this->sortFilters();

        $this->addTaxonFilter($aggregations, $taxon);
        $this->addMainTaxonFilter($aggregations, $taxon);

        $this->addPriceFilter($aggregations);
    }

    /**
     * @return Result[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return int
     */
    public function getTotalHits(): int
    {
        return $this->totalHits;
    }

    /**
     * @return Pagerfanta
     */
    public function getPager(): Pagerfanta
    {
        return $this->pager;
    }

    /**
     * @return Filter|null
     */
    public function getTaxonFilter(): ?Filter
    {
        return $this->taxonFilter;
    }

    /**
     * @return Filter|null
     */
    public function getMainTaxonFilter(): ?Filter
    {
        return $this->mainTaxonFilter;
    }

    /**
     * @return RangeFilter|null
     */
    public function getPriceFilter(): ?RangeFilter
    {
        return $this->priceFilter;
    }

    /**
     * Sort filters.
     */
    protected function sortFilters(): void
    {
        usort($this->filters, function($filter1, $filter2) {
            /** @var Filter $filter1 */
            /** @var Filter $filter2 */

            // If same count we display the filters with more values before
            if ($filter1->getCount() === $filter2->getCount()) {
                return \count($filter2->getValues()) > \count($filter1->getValues());
            }

            return $filter2->getCount() > $filter1->getCount();
        });
    }

    /**
     * Add taxon filter depending on aggregations.
     *
     * @param array $aggregations
     * @param TaxonInterface|null $taxon
     */
    protected function addTaxonFilter(array $aggregations, ?TaxonInterface $taxon): void
    {
        $taxonAggregation = $aggregations['taxons'] ?? null;
        if ($taxonAggregation && $taxonAggregation['doc_count'] > 0) {
            // Get current taxon level to retrieve only greater levels, in search we will take only the first level
            $currentTaxonLevel = $taxon ? $taxon->getLevel() : 0;

            // Get children taxon if we have current taxon
            $childrenTaxon = [];
            if ($taxon) {
                foreach ($taxon->getChildren() as $child) {
                    $childrenTaxon[$child->getCode()] = $child->getLevel();
                }
            }

            $filter = new Filter('taxon', 'monsieurbiz_searchplugin.filters.taxon_filter', $taxonAggregation['doc_count']);

            // Get taxon code in aggregation
            $taxonCodeBuckets = $taxonAggregation['codes']['buckets'] ?? [];
            foreach ($taxonCodeBuckets as $taxonCodeBucket) {
                if (0 === $taxonCodeBucket['doc_count']) {
                    continue;
                }
                $taxonCode = $taxonCodeBucket['key'];
                $taxonName = null;

                // Get taxon level in aggregation
                $taxonLevelBuckets = $taxonCodeBucket['levels']['buckets'] ?? [];
                foreach ($taxonLevelBuckets as $taxonLevelBucket) {
                    $level = $taxonLevelBucket['key'];
                    if ($level === ($currentTaxonLevel + 1) && (!$taxon || isset($childrenTaxon[$taxonCode]))) {
                        // Get taxon name in aggregation
                        $taxonNameBuckets = $taxonLevelBucket['names']['buckets'] ?? [];
                        foreach ($taxonNameBuckets as $taxonNameBucket) {
                            $taxonName = $taxonNameBucket['key'];
                            $filter->addValue($taxonName ?? $taxonCode, $taxonCodeBucket['doc_count']);
                            break 2;
                        }
                    }
                }
            }

            // Put taxon filter in first if contains value
            if (\count($filter->getValues())) {
                $this->taxonFilter = $filter;
            }
        }
    }

    /**
     * Add main taxon filter depending on aggregations.
     *
     * @param array $aggregations
     * @param TaxonInterface|null $taxon
     */
    protected function addMainTaxonFilter(array $aggregations, ?TaxonInterface $taxon): void
    {
        $taxonAggregation = $aggregations['mainTaxon'] ?? null;
        if ($taxonAggregation && $taxonAggregation['doc_count'] > 0) {
            $filter = new Filter('main_taxon', 'monsieurbiz_searchplugin.filters.taxon_filter', $taxonAggregation['doc_count']);

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
            if (\count($filter->getValues())) {
                $this->mainTaxonFilter = $filter;
            }
        }
    }

    /**
     * Add price filter depending on aggregations.
     *
     * @param array $aggregations
     */
    protected function addPriceFilter(array $aggregations): void
    {
        $priceAggregation = $aggregations['price'] ?? null;
        if ($priceAggregation && $priceAggregation['doc_count'] > 0) {
            $this->priceFilter = new RangeFilter(
                'price',
                'monsieurbiz_searchplugin.filters.price_filter',
                'monsieurbiz_searchplugin.filters.price_min',
                'monsieurbiz_searchplugin.filters.price_max',
                (int) floor(($priceAggregation['values']['min'] ?? 0) / 100),
                (int) ceil(($priceAggregation['values']['max'] ?? 0) / 100)
            );
        }
    }
}
