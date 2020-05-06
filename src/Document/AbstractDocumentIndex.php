<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Document;

use JoliCode\Elastically\Client;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Provider\DocumentRepositoryProvider;


abstract class AbstractDocumentIndex
{
    const DOCUMENT_INDEX_NAME = 'documents';

    /**
     * @var DocumentRepositoryProvider
     */
    protected $documentRepositoryProvider;

    /** @var Client */
    private $client;

    /**
     * PopulateCommand constructor.
     * @param Client $client
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * Get the client
     *
     * @return Client
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Retrieve the index name
     *
     * @param string $locale
     * @return string
     */
    protected function getIndexName(string $locale): string
    {
        return self::DOCUMENT_INDEX_NAME . '-' . strtolower($locale);
    }

    /**
     * @return IndexBuilder
     */
    protected function getIndexBuilder(): IndexBuilder
    {
        return $this->client->getIndexBuilder();
    }

    /**
     * @return Indexer
     */
    protected function getIndexer(): Indexer
    {
        return $this->client->getIndexer();
    }
}
