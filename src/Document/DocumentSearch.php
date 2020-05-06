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


class DocumentSearch extends AbstractDocumentIndex
{
    /** @var SearchRequestProvider */
    private $searchRequestProvider;

    /** @var LoggerInterface */
    private $logger;

    /**
     * PopulateCommand constructor.
     * @param Client $client
     * @param SearchRequestProvider $searchRequestProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        SearchRequestProvider $searchRequestProvider,
        LoggerInterface $logger
    ) {
        parent::__construct($client);
        $this->searchRequestProvider = $searchRequestProvider;
        $this->logger = $logger;
    }

    /**
     * Search documents for a given locale, query and a max number items
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
            /** @var ElasticallyResultSet $results */
            $results = $this->getClient()->getIndex($this->getIndexName($locale))->search(
                json_decode($this->getSearchJson($query, $page, $maxItems), true), $maxItems
            );
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        } catch (HttpException  $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        } catch (ResponseException  $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, $page);
        }

        return new ResultSet($maxItems, $page, $results);
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
            /** @var ElasticallyResultSet $results */
            $results = $this->getClient()->getIndex($this->getIndexName($locale))->search(
                json_decode($this->getInstantJson($query), true), $maxItems
            );
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, 1);
        }

        return new ResultSet($maxItems, 1, $results);
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

        return $elasticJson;
    }
}
