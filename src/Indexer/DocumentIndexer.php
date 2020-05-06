<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Indexer;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use JoliCode\Elastically\ResultSet as ElasticallyResultSet;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingParamException;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadOnlyIndexException;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentResult;
use Elastica\Document;
use JoliCode\Elastically\Client;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Model\ResultSet;
use Psr\Log\LoggerInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchRequestProvider;
use MonsieurBiz\SyliusSearchPlugin\Provider\DocumentRepositoryProvider;
use Webmozart\Assert\Assert;


class DocumentIndexer
{
    const DOCUMENT_INDEX_NAME = 'documents';

    /**
     * @var DocumentRepositoryProvider
     */
    protected $documentRepositoryProvider;

    /** @var Client */
    private $client;

    /** @var RepositoryInterface */
    private $localeRepository;

    /** @var SearchRequestProvider */
    private $searchRequestProvider;

    /** @var array */
    private $locales = [];

    /** @var LoggerInterface */
    private $logger;

    /**
     * PopulateCommand constructor.
     * @param Client $client
     * @param DocumentRepositoryProvider $documentRepositoryProvider
     * @param RepositoryInterface $localeRepository
     * @param SearchRequestProvider $searchRequestProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        DocumentRepositoryProvider $documentRepositoryProvider,
        RepositoryInterface $localeRepository,
        SearchRequestProvider $searchRequestProvider,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->documentRepositoryProvider = $documentRepositoryProvider;
        $this->localeRepository = $localeRepository;
        $this->searchRequestProvider = $searchRequestProvider;
        $this->logger = $logger;
    }

    /**
     * Retrieve all available locales
     *
     * @return array
     */
    public function getLocales(): array
    {
        if (empty($this->locales)) {
            $locales = $this->localeRepository->findAll();
            $this->locales = array_map(
                function (LocaleInterface $locale) {
                    return $locale->getCode();
                },
                $locales
            );
        }
        return $this->locales;
    }

    /**
     * Index all documents in all locales
     *
     * @throws \Exception
     */
    public function indexAll(): void
    {
        foreach ($this->getLocales() as $locale) {
            $this->indexAllByLocale($locale);
        }
    }

    /**
     * Index all document for a locale
     *
     * @param string $locale
     * @throws \Exception
     */
    public function indexAllByLocale(string $locale): void
    {
        $this->getIndexBuilder()->markAsLive(
            $this->getIndexBuilder()->createIndex($this->getIndexName($locale)),
            $this->getIndexName($locale)
        );

        $repositories = $this->documentRepositoryProvider->getRepositories();
        foreach ($repositories as $repository) {
            $documents = $repository->findAll();
            /** @var DocumentableInterface $document */
            foreach ($documents as $document) {
                Assert::isInstanceOf($document, DocumentableInterface::class);
                $this->indexOneByLocale($document->convertToDocument($locale), $locale);
            }
        }

        $this->getIndexer()->flush();
        $this->getIndexer()->refresh($this->getIndexName($locale));
        try {
            $this->getIndexBuilder()->purgeOldIndices($this->getIndexName($locale));
        } catch(ResponseException $exception) {
            throw new ReadOnlyIndexException($exception->getMessage());
        }
    }

    /**
     * Index a document for all locales
     *
     * @param DocumentableInterface $subject
     * @throws \Exception
     */
    public function indexOne(DocumentableInterface $subject): void
    {
        foreach ($this->getLocales() as $locale) {
            $this->indexOneByLocale($subject->convertToDocument($locale), $locale);
            $this->getIndexer()->flush();
        }
    }

    /**
     * Index a document for one locale
     *
     * @param DocumentResult $document
     * @param string $locale
     * @throws MissingParamException
     */
    public function indexOneByLocale(DocumentResult $document, string $locale): void
    {
        $this->getIndexer()->scheduleIndex(
            $this->getIndexName($locale),
            new Document($document->getUniqId(), $document)
        );
    }

    /**
     * Remove a document for all locales
     *
     * @param DocumentableInterface $subject
     * @throws \Exception
     */
    public function removeOne(DocumentableInterface $subject): void
    {
        foreach ($this->getLocales() as $locale) {
            $this->removeOneByLocale($subject->convertToDocument($locale), $locale);
            $this->getIndexer()->flush();
        }
    }

    /**
     * Remove a document for all locales
     *
     * @param DocumentResult $document
     * @param string $locale
     * @throws MissingParamException
     */
    public function removeOneByLocale(DocumentResult $document, string $locale): void
    {
        $this->getIndexer()->scheduleDelete(
            $this->getIndexName($locale),
            $document->getUniqId()
        );
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
            $results = $this->client->getIndex($this->getIndexName($locale))->search(
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
            $results = $this->client->getIndex($this->getIndexName($locale))->search(
                json_decode($this->getInstantJson($query), true), $maxItems
            );
        } catch (ReadFileException $exception) {
            $this->logger->critical($exception->getMessage());
            return new ResultSet($maxItems, 1);
        }

        return new ResultSet($maxItems, 1, $results);
    }

    /**
     * Retrieve the index name
     *
     * @param string $locale
     * @return string
     */
    private function getIndexName(string $locale): string
    {
        return self::DOCUMENT_INDEX_NAME . '-' . strtolower($locale);
    }

    /**
     * @return IndexBuilder
     */
    private function getIndexBuilder(): IndexBuilder
    {
        return $this->client->getIndexBuilder();
    }

    /**
     * @return Indexer
     */
    private function getIndexer(): Indexer
    {
        return $this->client->getIndexer();
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
