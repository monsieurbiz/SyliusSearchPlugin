<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document\Index;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use JoliCode\Elastically\ResultSet as ElasticallyResultSet;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use JoliCode\Elastically\Client;
use MonsieurBiz\SyliusSearchPlugin\Helper\AggregationHelper;
use MonsieurBiz\SyliusSearchPlugin\Helper\SortHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultSet;
use Psr\Log\LoggerInterface;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchQueryProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Yaml\Yaml;


class Search extends AbstractIndex
{
    /** @var SearchQueryProvider */
    private $searchQueryProvider;

    /** @var LoggerInterface */
    private $logger;

    /** @var ChannelContextInterface */
    private $channelContext;

    /**
     * PopulateCommand constructor.
     * @param Client $client
     * @param SearchQueryProvider $searchQueryProvider
     * @param ChannelContextInterface $channelContext
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        SearchQueryProvider $searchQueryProvider,
        ChannelContextInterface $channelContext,
        LoggerInterface $logger
    ) {
        parent::__construct($client);
        $this->searchQueryProvider = $searchQueryProvider;
        $this->channelContext = $channelContext;
        $this->logger = $logger;
    }

    /**
     * Search documents for a given locale, search terms, max number items and page
     *
     * @param string $locale
     * @param string $search
     * @param int $maxItems
     * @param int $page
     * @param array $sorting
     * @param array $filters
     * @return ResultSet
     */
    public function search(string $locale, string $search, int $maxItems, int $page, array $sorting, array $filters): ResultSet
    {
        try {
            return $this->query($locale, $this->getSearchQuery($search, $page, $maxItems, $sorting, $filters), $maxItems, $page);
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        }
    }

    /**
     * Instant search documents for a given locale, query and a max number items
     *
     * @param string $locale
     * @param string $search
     * @param int $maxItems
     * @return ResultSet
     */
    public function instant(string $locale, string $search, int $maxItems): ResultSet
    {
        try {
            return $this->query($locale, $this->getInstantQuery($search), $maxItems, 1);
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, 1);
        }
    }

    /**
     * Taxon search documents for a given locale, taxon code, max number items and page
     *
     * @param string $locale
     * @param string $taxon
     * @param int $maxItems
     * @param int $page
     * @param array $sorting
     * @param array $filters
     * @return ResultSet
     */
    public function taxon(string $locale, string $taxon, int $maxItems, int $page, array $sorting, array $filters): ResultSet
    {
        try {
            return $this->query($locale, $this->getTaxonQuery($taxon, $page, $maxItems, $sorting, $filters), $maxItems, $page);
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        }
    }

    /**
     * Perform search for a given query
     *
     * @param string $locale
     * @param array $query
     * @param int $maxItems
     * @param int $page
     * @return ResultSet
     */
    private function query(string $locale, array $query, int $maxItems, int $page)
    {
        try {
            /** @var ElasticallyResultSet $results */
            $results = $this->getClient()->getIndex($this->getIndexName($locale))->search(
                $query, $maxItems
            );
        } catch (HttpException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        } catch (ResponseException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        }

        return new ResultSet($maxItems, $page, $results);
    }

    /**
     * Retrieve the query to send to Elasticsearch for search
     *
     * @param string $search
     * @param int $page
     * @param int $size
     * @param array $sorting
     * @param array $filters
     * @return array
     * @throws ReadFileException
     */
    private function getSearchQuery(string $search, int $page, int $size, array $sorting, array $filters): array
    {
        $query = $this->searchQueryProvider->getSearchQuery();

        // Replace params
        $query = str_replace('{{QUERY}}', $search, $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        // Manage limits
        $from = ($page - 1) * $size;
        $query['from'] = max(0, $from);
        $query['size'] =  max(1, $size);

        // Manage sorting
        $channelCode = $this->channelContext->getChannel()->getCode();
        foreach ($sorting as $field => $order) {
            $query['sort'][] = SortHelper::getSortParamByField($field, $channelCode, $order);
            break; // only 1
        }

        // Manage filters
        $query['aggs'] = AggregationHelper::buildAggregations($filters);

        return $query;
    }

    /**
     * Retrieve the query to send to Elasticsearch for instant search
     *
     * @param string $search
     * @return array
     * @throws ReadFileException
     */
    private function getInstantQuery(string $search): array
    {
        $query = $this->searchQueryProvider->getInstantQuery();

        // Replace params
        $query = str_replace('{{QUERY}}', $search, $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        return $query;
    }

    /**
     * Retrieve the query to send to Elasticsearch for taxon search
     *
     * @param string $taxon
     * @param int $page
     * @param int $size
     * @param array $sorting
     * @param array $filters
     * @return array
     * @throws ReadFileException
     */
    private function getTaxonQuery(string $taxon, int $page, int $size, array $sorting, array $filters): array
    {
        $query = $this->searchQueryProvider->getTaxonQuery();

        // Replace params
        $query = str_replace('{{TAXON}}', $taxon, $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        // Manage limits
        $from = ($page - 1) * $size;
        $query['from'] = max(0, $from);
        $query['size'] =  max(1, $size);

        // Manage sorting
        $channelCode = $this->channelContext->getChannel()->getCode();
        foreach ($sorting as $field => $order) {
            $query['sort'][] = SortHelper::getSortParamByField($field, $channelCode, $order, $taxon);
            break; // only 1
        }

        // Manage filters
        $query['aggs'] = AggregationHelper::buildAggregations($filters);

        return $query;
    }

    /**
     * @param string $query
     * @return array
     */
    private function parseQuery(string $query): array
    {
        return Yaml::parse($query);
    }
}
