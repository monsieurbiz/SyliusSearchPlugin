<?php

declare(strict_types=1);

namespace Monsieurbiz\SyliusSearchPlugin\Indexer;

use Elastica\Exception\Connection\HttpException;
use Monsieurbiz\SyliusSearchPlugin\Exception\MissingParamException;
use Monsieurbiz\SyliusSearchPlugin\Exception\ReadFileException;
use Monsieurbiz\SyliusSearchPlugin\Model\DocumentableInterface;
use Monsieurbiz\SyliusSearchPlugin\Model\DocumentResult;
use Elastica\Document;
use JoliCode\Elastically\Client;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Monsieurbiz\SyliusSearchPlugin\Provider\SearchRequestProvider;
use Monsieurbiz\SyliusSearchPlugin\Provider\DocumentRepositoryProvider;
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

    /**
     * PopulateCommand constructor.
     * @param Client $client
     * @param DocumentRepositoryProvider $documentRepositoryProvider
     * @param RepositoryInterface $localeRepository
     * @param SearchRequestProvider $searchRequestProvider
     */
    public function __construct(
        Client $client,
        DocumentRepositoryProvider $documentRepositoryProvider,
        RepositoryInterface $localeRepository,
        SearchRequestProvider $searchRequestProvider
    ) {
        $this->client = $client;
        $this->documentRepositoryProvider = $documentRepositoryProvider;
        $this->localeRepository = $localeRepository;
        $this->searchRequestProvider = $searchRequestProvider;
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
        $this->getIndexBuilder()->purgeOldIndices($this->getIndexName($locale));
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
     * @return array
     */
    public function search(string $locale, string $query, int $maxItems, int $page)
    {
        try {
            $results = $this->client->getIndex($this->getIndexName($locale))->search(
                json_decode($this->getSearch($query, $page, $maxItems), true), $maxItems, $page
            );
        } catch (ReadFileException $exception) {
            $results = [];
        } catch (HttpException  $exception) {
            $results = [];
        }

        $searchResults = [
            'total' => $results->getTotalHits(),
            'results' => []
        ];

        foreach ($results as $result) {
            $searchResults['results'][] = $result->getModel();
        }

        return $searchResults;
    }

    /**
     * Instant search documents for a given locale, query and a max number items
     *
     * @param string $locale
     * @param string $query
     * @param int $maxItems
     * @return array
     */
    public function instant(string $locale, string $query, int $maxItems): array
    {
        try {
            $results = $this->client->getIndex($this->getIndexName($locale))->search(
                json_decode($this->getInstant($query), true), $maxItems
            );
        } catch (ReadFileException $exception) {
            $results = [];
        }

        $searchResults = [];
        foreach ($results as $result) {
            $searchResults[] = $result->getModel();
        }

        return $searchResults;
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
     * @param null $query
     * @param int $page
     * @return mixed|string
     * @throws ReadFileException
     */
    private function getSearch($query = null, int $page = 0, $max)
    {
        $elasticJson = $this->searchRequestProvider->getSearchJson();

        if ($query) {
            $elasticJson = str_replace('{{QUERY}}', $query, $elasticJson);
        }

        if ($page) {
            $elasticJson = str_replace('{{FROM}}', $page * $max - $max, $elasticJson);
        }

        return $elasticJson;
    }

    /**
     * Retrieve the JSON to send to Elasticsearch for instant search
     *
     * @param null $query
     * @return mixed|string
     * @throws ReadFileException
     */
    private function getInstant($query = null)
    {
        $elasticJson = $this->searchRequestProvider->getInstantJson();

        if ($query) {
            $elasticJson = str_replace('{{QUERY}}', $query, $elasticJson);
        }

        return $elasticJson;
    }
}
