<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Document;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use JoliCode\Elastically\ResultSet as ElasticallyResultSet;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use JoliCode\Elastically\Client;
use MonsieurBiz\SyliusSearchPlugin\Model\ResultSet;
use Psr\Log\LoggerInterface;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchRequestProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;


class DocumentSearch extends AbstractDocumentIndex
{
    /** @var SearchRequestProvider */
    private $searchRequestProvider;

    /** @var LoggerInterface */
    private $logger;

    /** @var ChannelContextInterface */
    private $channelContext;

    /**
     * PopulateCommand constructor.
     * @param Client $client
     * @param SearchRequestProvider $searchRequestProvider
     * @param ChannelContextInterface $channelContext
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        SearchRequestProvider $searchRequestProvider,
        ChannelContextInterface $channelContext,
        LoggerInterface $logger
    ) {
        parent::__construct($client);
        $this->searchRequestProvider = $searchRequestProvider;
        $this->channelContext = $channelContext;
        $this->logger = $logger;
    }

    /**
     * Search documents for a given locale, query and, max number items and page
     *
     * @param string $locale
     * @param string $query
     * @param int $maxItems
     * @param int $page
     * @return ResultSet
     */
    public function search(string $locale, string $query, int $maxItems, int $page): ResultSet
    {
        try {
            return $this->jsonSearch($locale, $this->getSearchJson($query, $page, $maxItems), $maxItems, $page);
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        }
    }

    /**
     * Instant search documents for a given locale, query and a max number items
     *
     * @param string $locale
     * @param string $query
     * @param int $maxItems
     * @return ResultSet
     */
    public function instant(string $locale, string $query, int $maxItems): ResultSet
    {
        try {
            return $this->jsonSearch($locale, $this->getInstantJson($query), $maxItems, 1);
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
     * @return ResultSet
     */
    public function taxon(string $locale, string $taxon, int $maxItems, int $page): ResultSet
    {
        try {
            return $this->jsonSearch($locale, $this->getTaxonJson($taxon, $page, $maxItems), $maxItems, $page);
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        }
    }

    /**
     * Perform search for a given JSON
     *
     * @param string $locale
     * @param string $json
     * @param int $maxItems
     * @param int $page
     * @return ResultSet
     */
    private function jsonSearch(string $locale, string $json, int $maxItems, int $page)
    {
        try {
            /** @var ElasticallyResultSet $results */
            $results = $this->getClient()->getIndex($this->getIndexName($locale))->search(
                json_decode($json, true), $maxItems
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
     * Retrieve the JSON to send to Elasticsearch for search
     *
     * @param string $query
     * @param int $page
     * @param int $size
     * @return string
     * @throws ReadFileException
     */
    private function getSearchJson(string $query, int $page, int $size): string
    {
        $elasticJson = $this->searchRequestProvider->getSearchJson();

        $from = ($page - 1) * $size;

        $elasticJson = str_replace('{{QUERY}}', $query, $elasticJson);
        $elasticJson = str_replace('{{FROM}}', max(0, $from), $elasticJson);
        $elasticJson = str_replace('{{SIZE}}', max(1, $size), $elasticJson);
        $elasticJson = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $elasticJson);

        return $elasticJson;
    }

    /**
     * Retrieve the JSON to send to Elasticsearch for instant search
     *
     * @param string $query
     * @return mixed|string
     * @throws ReadFileException
     */
    private function getInstantJson(string $query)
    {
        $elasticJson = $this->searchRequestProvider->getInstantJson();
        $elasticJson = str_replace('{{QUERY}}', $query, $elasticJson);
        $elasticJson = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $elasticJson);

        return $elasticJson;
    }

    /**
     * Retrieve the JSON to send to Elasticsearch for taxon search
     *
     * @param string $taxon
     * @param int $page
     * @param int $size
     * @return mixed|string
     * @throws ReadFileException
     */
    private function getTaxonJson(string $taxon, int $page, int $size): string
    {
        $elasticJson = $this->searchRequestProvider->getTaxonJson();

        $from = ($page - 1) * $size;

        $elasticJson = str_replace('{{TAXON}}', $taxon, $elasticJson);
        $elasticJson = str_replace('{{FROM}}', max(0, $from), $elasticJson);
        $elasticJson = str_replace('{{SIZE}}', max(1, $size), $elasticJson);
        $elasticJson = str_replace('{{CHANNEL}}', $this->channelContext->getChannel()->getCode(), $elasticJson);

        return $elasticJson;
    }
}
