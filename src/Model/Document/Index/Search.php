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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document\Index;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use Elastica\ResultSet as ElasticaResultSet;
use JoliCode\Elastically\Client;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use MonsieurBiz\SyliusSearchPlugin\Helper\AggregationHelper;
use MonsieurBiz\SyliusSearchPlugin\Helper\FilterHelper;
use MonsieurBiz\SyliusSearchPlugin\Helper\SortHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\ArrayObject;
use MonsieurBiz\SyliusSearchPlugin\Model\Config\GridConfig;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultSet;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchQueryProvider;
use Psr\Log\LoggerInterface;
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
     *
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
     * Search documents for a given locale, search terms, max number items and page.
     *
     * @param GridConfig $gridConfig
     *
     * @return ResultSet
     */
    public function search(GridConfig $gridConfig): ResultSet
    {
        try {
            return $this->query($gridConfig, $this->getSearchQuery($gridConfig));
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());

            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }
    }

    /**
     * Instant search documents for a given locale, query and a max number items.
     *
     * @param GridConfig $gridConfig
     *
     * @return ResultSet
     */
    public function instant(GridConfig $gridConfig): ResultSet
    {
        try {
            return $this->query($gridConfig, $this->getInstantQuery($gridConfig));
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());

            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }
    }

    /**
     * Taxon search documents for a given locale, taxon code, max number items and page.
     *
     * @param GridConfig $gridConfig
     *
     * @return ResultSet
     */
    public function taxon(GridConfig $gridConfig): ResultSet
    {
        try {
            return $this->query($gridConfig, $this->getTaxonQuery($gridConfig));
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());

            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }
    }

    /**
     * Perform search for a given query.
     *
     * @param GridConfig $gridConfig
     * @param array $query
     *
     * @return ResultSet
     */
    private function query(GridConfig $gridConfig, array $query)
    {
        try {
            /** @var ElasticaResultSet $results */
            $results = $this->getClient()->getIndex($this->getIndexName($gridConfig->getLocale()))->search(
                $query, $gridConfig->getLimit()
            );
        } catch (HttpException $exception) {
            $this->logger->critical($exception->getMessage());

            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        } catch (ResponseException $exception) {
            $this->logger->critical($exception->getMessage());

            return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage());
        }

        return new ResultSet($gridConfig->getLimit(), $gridConfig->getPage(), $results, $gridConfig->getTaxon());
    }

    /**
     * Retrieve the query to send to Elasticsearch for search.
     *
     * @param GridConfig $gridConfig
     *
     * @throws ReadFileException
     *
     * @return array
     */
    private function getSearchQuery(GridConfig $gridConfig): array
    {
        $query = $this->searchQueryProvider->getSearchQuery();

        // Replace params
        $query = str_replace('{{QUERY}}', $gridConfig->getQuery(), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        $appliedFilters = FilterHelper::buildFilters($gridConfig->getAppliedFilters());
        if ($gridConfig->haveToApplyManuallyFilters() && isset($appliedFilters['bool']['filter'])) {
            // Will retrieve filters after we applied the current ones
            $query['query']['bool']['filter'] = array_merge(
                $query['query']['bool']['filter'], $appliedFilters['bool']['filter']
            );
        } elseif (!empty($appliedFilters)) {
            // Will retrieve filters before we applied the current ones
            $query['post_filter'] = new ArrayObject($appliedFilters); // Use custom ArrayObject because Elastica make `toArray` on it.
        }

        // Manage limits
        $from = ($gridConfig->getPage() - 1) * $gridConfig->getLimit();
        $query['from'] = max(0, $from);
        $query['size'] = max(1, $gridConfig->getLimit());

        // Manage sorting
        $channelCode = $this->channelContext->getChannel()->getCode();
        foreach ($gridConfig->getSorting() as $field => $order) {
            $query['sort'][] = SortHelper::getSortParamByField($field, $channelCode, $order);
            break; // only 1
        }

        // Manage filters
        $aggs = AggregationHelper::buildAggregations($gridConfig->getFilters());
        if (!empty($aggs)) {
            $query['aggs'] = AggregationHelper::buildAggregations($gridConfig->getFilters());
        }

        return $query;
    }

    /**
     * Retrieve the query to send to Elasticsearch for instant search.
     *
     * @param GridConfig $gridConfig
     *
     * @throws ReadFileException
     *
     * @return array
     */
    private function getInstantQuery(GridConfig $gridConfig): array
    {
        $query = $this->searchQueryProvider->getInstantQuery();

        // Replace params
        $query = str_replace('{{QUERY}}', $gridConfig->getQuery(), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        return $this->parseQuery($query);
    }

    /**
     * Retrieve the query to send to Elasticsearch for taxon search.
     *
     * @param GridConfig $gridConfig
     *
     * @throws ReadFileException
     *
     * @return array
     */
    private function getTaxonQuery(GridConfig $gridConfig): array
    {
        $query = $this->searchQueryProvider->getTaxonQuery();

        // Replace params
        $query = str_replace('{{TAXON}}', $gridConfig->getTaxon()->getCode(), $query);
        $query = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $query);

        // Convert query to array
        $query = $this->parseQuery($query);

        // Apply filters
        $appliedFilters = FilterHelper::buildFilters($gridConfig->getAppliedFilters());
        if ($gridConfig->haveToApplyManuallyFilters() && isset($appliedFilters['bool']['filter'])) {
            // Will retrieve filters after we applied the current ones
            $query['query']['bool']['filter'] = array_merge(
                $query['query']['bool']['filter'], $appliedFilters['bool']['filter']
            );
        } elseif (!empty($appliedFilters)) {
            // Will retrieve filters before we applied the current ones
            $query['post_filter'] = new ArrayObject($appliedFilters); // Use custom ArrayObject because Elastica make `toArray` on it.
        }

        // Manage limits
        $from = ($gridConfig->getPage() - 1) * $gridConfig->getLimit();
        $query['from'] = max(0, $from);
        $query['size'] = max(1, $gridConfig->getLimit());

        // Manage sorting
        $channelCode = $this->channelContext->getChannel()->getCode();
        foreach ($gridConfig->getSorting() as $field => $order) {
            $query['sort'][] = SortHelper::getSortParamByField($field, $channelCode, $order, $gridConfig->getTaxon()->getCode());
            break; // only 1
        }

        // Manage filters
        $aggs = AggregationHelper::buildAggregations($gridConfig->getFilters());
        if (!empty($aggs)) {
            $query['aggs'] = AggregationHelper::buildAggregations($gridConfig->getFilters());
        }

        return $query;
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function parseQuery(string $query): array
    {
        return Yaml::parse($query);
    }
}
