<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

use JoliCode\Elastically\ResultSet as ElasticallyResultSet;
use JoliCode\Elastically\Result;
use MonsieurBiz\SyliusSearchPlugin\Adapter\ResultSetAdapter;
use Pagerfanta\Pagerfanta;

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

    /** @var Pagerfanta */
    private $pager;

    /**
     * SearchResults constructor.
     * @param int $maxItems
     * @param int $page
     * @param ElasticallyResultSet|null $resultSet
     */
    public function __construct(int $maxItems, int $page, ?ElasticallyResultSet $resultSet = null)
    {
        $this->maxItems = $maxItems;
        $this->page = $page;

        // Empty result set
        if ($resultSet === null) {
            $this->totalHits = 0;
            $this->results = [];
            $this->filters = [];
        } else {
            /** @var Result $result */
            foreach ($resultSet as $result) {
                $this->results[] = $result->getModel();
            }
            $this->totalHits = $resultSet->getTotalHits();
            $this->initFilters($resultSet);
        }

        $this->initPager();
    }

    /**
     * Init pager with Pager Fanta
     */
    private function initPager()
    {
        $adapter = new ResultSetAdapter($this);
        $this->pager = new Pagerfanta($adapter);
        $this->pager->setMaxPerPage($this->maxItems);
        $this->pager->setCurrentPage($this->page);
    }

    /**
     * Init filters array depending on result aggregations
     *
     * @param ElasticallyResultSet $resultSet
     */
    private function initFilters(ElasticallyResultSet $resultSet)
    {
        $aggregations = $resultSet->getAggregations();


        // Retrieve filters labels in aggregations
        $attributes = [];
        $attributeAggregations = $aggregations['attributes'];
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
        $filterAggregations = $aggregations['filters'];
        unset($filterAggregations['doc_count']);
        foreach ($filterAggregations as $field => $aggregation) {
            if ($aggregation['doc_count'] === 0) {
                continue;
            }
            $filter = new Filter($attributes[$field] ?? $field, $aggregation['doc_count']);
            $buckets = $aggregation['values']['buckets'] ?? [];
            foreach ($buckets as $bucket) {
                if (isset($bucket['key']) && isset($bucket['doc_count'])) {
                    $filter->addValue($bucket['key'], $bucket['doc_count']);
                }
            }
            $this->filters[] = $filter;
        }
        $this->sortFilters();
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

    private function sortFilters()
    {
        usort($this->filters, function($filter1, $filter2) {
            /** @var Filter $filter1 */
            /** @var Filter $filter2 */
            return $filter2->getCount() > $filter1->getCount();
        } );
    }
}
