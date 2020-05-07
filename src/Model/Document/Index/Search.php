<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document\Index;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use JoliCode\Elastically\ResultSet as ElasticallyResultSet;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use JoliCode\Elastically\Client;
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
     * @return ResultSet
     */
    public function search(string $locale, string $search, int $maxItems, int $page, array $sorting): ResultSet
    {
        try {
            return $this->query($locale, $this->getSearchQuery($search, $page, $maxItems, $sorting), $maxItems, $page);
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
     * @return ResultSet
     */
    public function taxon(string $locale, string $taxon, int $maxItems, int $page, array $sorting): ResultSet
    {
        try {
            return $this->query($locale, $this->getTaxonQuery($taxon, $page, $maxItems, $sorting), $maxItems, $page);
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
     * @return string
     * @throws ReadFileException
     */
    private function getSearchQuery(string $search, int $page, int $size, array $sorting): array
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
        foreach ($sorting as $field => $order) {
            $query['sort'][] = $this->getSortParamByField($field, $order);
            break; // only 1
        }

        return $query;
    }

    /**
     * Retrieve the query to send to Elasticsearch for instant search
     *
     * @param string $search
     * @return mixed|string
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
     * @return mixed|string
     * @throws ReadFileException
     */
    private function getTaxonQuery(string $taxon, int $page, int $size, array $sorting): array
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
        foreach ($sorting as $field => $order) {
            $query['sort'][] = $this->getSortParamByField($field, $order, $taxon);
            break; // only 1
        }

        return $query;
    }

    /**
     * Get query's sort array depending on sorted field
     *
     * @param string $field
     * @param string $order
     * @param string $taxon
     * @return array
     */
    private function getSortParamByField(string $field, string $order = 'asc', string $taxon = ''): array
    {
        switch($field) {
            case 'name':
                return $this->buildSort('attributes.value.keyword', $order, 'attributes', 'attributes.code', $field);
            case 'created_at':
                return $this->buildSort('attributes.value.keyword', $order, 'attributes', 'attributes.code', $field);
            case 'price':
                return $this->buildSort('price.value', $order, 'price', 'price.channel', $this->channelContext->getChannel()->getCode());
            case 'position':
                return $this->buildSort('taxon.position', $order, 'taxon', 'taxon.code', $taxon);
            default:
                // Dummy value to have null sorting in ES and keep ES results sorting
                return $this->buildSort('attributes.value.keyword', $order, 'attributes', 'attributes.code', 'dummy');
        }
    }

    /**
     * Build sort array to add in query
     *
     * @param string $field
     * @param string $order
     * @param string $nestedPath
     * @param string $sortFilterField
     * @param string $sortFilterValue
     * @return array
     */
    private function buildSort(
        string $field,
        string $order,
        string $nestedPath,
        string $sortFilterField,
        string $sortFilterValue
    ): array {
        return [
            $field => [
                'order' => $order,
                'nested' => [
                    'path' => $nestedPath,
                    'filter' => [
                        'term' => [$sortFilterField => $sortFilterValue]
                    ]
                ]
            ]
        ];
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
