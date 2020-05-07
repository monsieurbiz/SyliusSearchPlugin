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
    private $results;

    /** @var int */
    private $totalHits;

    /** @var int */
    private $maxItems;

    /** @var int */
    private $page;

    /** @var Pagerfanta */
    private $pager;

    /**
     * SearchResults constructor.
     * @param int $maxItems
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
        } else {
            /** @var Result $result */
            foreach ($resultSet as $result) {
                $this->results[] = $result->getModel();
            }
            $this->totalHits = $resultSet->getTotalHits();
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
     * @return Result[]
     */
    public function getResults(): array
    {
        return $this->results;
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
}
