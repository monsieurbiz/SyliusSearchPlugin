<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document\Index;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use JoliCode\Elastically\ResultSet as ElasticallyResultSet;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use JoliCode\Elastically\Client;
use MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon;
use MonsieurBiz\SyliusSearchPlugin\Helper\AggregationHelper;
use MonsieurBiz\SyliusSearchPlugin\Helper\SortHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\Config\GridConfig;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultSet;
use Psr\Log\LoggerInterface;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchQueryProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\TaxonInterface;
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
     * @param GridConfig $gridConfig
     * @return ResultSet
     */
    public function search(GridConfig $gridConfig): ResultSet
    {
        try {
            return $this->query(
                $gridConfig->getLocale(),
                $this->getSearchQuery($gridConfig),
                $gridConfig->getLimit(),
                $gridConfig->getPage()
            );
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }
    }

    /**
     * Instant search documents for a given locale, query and a max number items
     *
     * @param GridConfig $gridConfig
     * @return ResultSet
     */
    public function instant(GridConfig $gridConfig): ResultSet
    {
        try {
            return $this->query(
                $gridConfig->getLocale(),
                $this->getInstantQuery($gridConfig),
                $gridConfig->getLimit(),
                $gridConfig->getPage()
            );
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }
    }

    /**
     * Taxon search documents for a given locale, taxon code, max number items and page
     *
     * @param GridConfig $gridConfig
     * @return ResultSet
     */
    public function taxon(GridConfig $gridConfig): ResultSet
    {
        try {
            return $this->query(
                $gridConfig->getLocale(),
                $this->getTaxonQuery($gridConfig),
                $gridConfig->getLimit(),
                $gridConfig->getPage(),
                $gridConfig->getTaxon()
            );
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }
    }

    /**
     * Perform search for a given query
     *
     * @param string $locale
     * @param array $query
     * @param int $maxItems
     * @param int $page
     * @param TaxonInterface|null $taxon
     * @return ResultSet
     */
    private function query(string $locale, array $query, int $maxItems, int $page, ?TaxonInterface $taxon = null)
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

        return new ResultSet($maxItems, $page, $results, $taxon);
    }

    /**
     * Retrieve the query to send to Elasticsearch for search
     *
     * @param GridConfig $gridConfig
     * @return array
     * @throws ReadFileException
     */
    private function getSearchQuery(GridConfig $gridConfig): array
    {
        $query = $this->searchQueryProvider->getSearchQuery();

        // Replace params
        $query = str_replace('{{QUERY}}', $gridConfig->getQuery(), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        // Manage limits
        $from = ($gridConfig->getPage() - 1) * $gridConfig->getLimit();
        $query['from'] = max(0, $from);
        $query['size'] =  max(1, $gridConfig->getLimit());

        // Manage sorting
        $channelCode = $this->channelContext->getChannel()->getCode();
        foreach ($gridConfig->getSorting() as $field => $order) {
            $query['sort'][] = SortHelper::getSortParamByField($field, $channelCode, $order);
            break; // only 1
        }

        // Manage filters
        $query['aggs'] = AggregationHelper::buildAggregations($gridConfig->getFilters());

        return $query;
    }

    /**
     * Retrieve the query to send to Elasticsearch for instant search
     *
     * @param GridConfig $gridConfig
     * @return array
     * @throws ReadFileException
     */
    private function getInstantQuery(GridConfig $gridConfig): array
    {
        $query = $this->searchQueryProvider->getInstantQuery();

        // Replace params
        $query = str_replace('{{QUERY}}', $gridConfig->getQuery(), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        return $query;
    }

    /**
     * Retrieve the query to send to Elasticsearch for taxon search
     *
     * @param GridConfig $gridConfig
     * @return array
     * @throws ReadFileException
     */
    private function getTaxonQuery(GridConfig $gridConfig): array
    {
        $query = $this->searchQueryProvider->getTaxonQuery();

        // Replace params
        $query = str_replace('{{TAXON}}', $gridConfig->getTaxon()->getCode(), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        // Manage limits
        $from = ($gridConfig->getPage() - 1) * $gridConfig->getLimit();
        $query['from'] = max(0, $from);
        $query['size'] =  max(1, $gridConfig->getLimit());

        // Manage sorting
        $channelCode = $this->channelContext->getChannel()->getCode();
        foreach ($gridConfig->getSorting() as $field => $order) {
            $query['sort'][] = SortHelper::getSortParamByField($field, $channelCode, $order, $gridConfig->getTaxon()->getCode());
            break; // only 1
        }

        // Manage filters
        $query['aggs'] = AggregationHelper::buildAggregations($gridConfig->getFilters());

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
