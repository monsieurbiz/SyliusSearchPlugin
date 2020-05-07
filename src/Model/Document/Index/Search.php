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
     * @param string $query
     * @param int $maxItems
     * @param int $page
     * @return ResultSet
     */
    private function query(string $locale, string $query, int $maxItems, int $page)
    {
        try {
            /** @var ElasticallyResultSet $results */
            $results = $this->getClient()->getIndex($this->getIndexName($locale))->search(
                Yaml::parse($query), $maxItems
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
    private function getSearchQuery(string $search, int $page, int $size, array $sorting): string
    {
        $query = $this->searchQueryProvider->getSearchQuery();

        $from = ($page - 1) * $size;

        $query = str_replace('{{QUERY}}', $search, $query);
        $query = str_replace('{{FROM}}', max(0, $from), $query);
        $query = str_replace('{{SIZE}}', max(1, $size), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        foreach ($sorting as $field => $order) {
            $query = str_replace('{{SORT_ORDER}}', $order, $query);
            $parameters = $this->getSortParamByField($field);
            $query = str_replace('{{SORT_FIELD}}', $parameters['sort_field'] ?? '', $query);
            $query = str_replace('{{SORT_NESTED_PATH}}', $parameters['sort_nested_path'] ?? '', $query);
            $query = str_replace('{{SORT_FILTER_FIELD}}', $parameters['sort_filter_field'] ?? '', $query);
            $query = str_replace('{{SORT_FILTER_VALUE}}', $parameters['sort_filter_value'] ?? '', $query);
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
    private function getInstantQuery(string $search)
    {
        $query = $this->searchQueryProvider->getInstantQuery();
        $query = str_replace('{{QUERY}}', $search, $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

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
    private function getTaxonQuery(string $taxon, int $page, int $size, array $sorting): string
    {
        $query = $this->searchQueryProvider->getTaxonQuery();

        $from = ($page - 1) * $size;

        $query = str_replace('{{TAXON}}', $taxon, $query);
        $query = str_replace('{{FROM}}', max(0, $from), $query);
        $query = str_replace('{{SIZE}}', max(1, $size), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        foreach ($sorting as $field => $order) {
            $query = str_replace('{{SORT_ORDER}}', $order, $query);
            $parameters = $this->getSortParamByField($field, $taxon);
            $query = str_replace('{{SORT_FIELD}}', $parameters['sort_field'] ?? '', $query);
            $query = str_replace('{{SORT_NESTED_PATH}}', $parameters['sort_nested_path'] ?? '', $query);
            $query = str_replace('{{SORT_FILTER_FIELD}}', $parameters['sort_filter_field'] ?? '', $query);
            $query = str_replace('{{SORT_FILTER_VALUE}}', $parameters['sort_filter_value'] ?? '', $query);
            break; // only 1
        }

        return $query;
    }

    /**
     * @param string $field
     * @param string $taxon
     * @return array
     */
    private function getSortParamByField(string $field, string $taxon = ''): array
    {
        switch($field) {
            case 'name':
                return [
                    'sort_field' => 'attributes.value.keyword',
                    'sort_nested_path' => 'attributes',
                    'sort_filter_field' => 'attributes.code',
                    'sort_filter_value' => $field,
                ];
            case 'created_at':
                return [
                    'sort_field' => 'attributes.value.keyword',
                    'sort_nested_path' => 'attributes',
                    'sort_filter_field' => 'attributes.code',
                    'sort_filter_value' => $field,
                ];
            case 'price':
                return [
                    'sort_field' => 'price.value',
                    'sort_nested_path' => 'price',
                    'sort_filter_field' => 'price.channel',
                    'sort_filter_value' => $this->channelContext->getChannel()->getCode(),
                ];
            case 'position':
                return [
                    'sort_field' => 'taxon.position',
                    'sort_nested_path' => 'taxon',
                    'sort_filter_field' => 'taxon.code',
                    'sort_filter_value' => $taxon,
                ];
            default:
                return [
                    'sort_field' => 'attributes.value.keyword',
                    'sort_nested_path' => 'attributes',
                    'sort_filter_field' => 'attributes.code',
                    'sort_filter_value' => 'dummy', // Dummy value to have null sorting in ES
                ];
        }
    }
}
