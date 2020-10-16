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

use JoliCode\Elastically\Client;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Provider\DocumentRepositoryProvider;

abstract class AbstractIndex
{
    public const DOCUMENT_INDEX_NAME = 'documents';

    /**
     * @var DocumentRepositoryProvider
     */
    protected $documentRepositoryProvider;

    /** @var Client */
    private $client;

    /**
     * PopulateCommand constructor.
     *
     * @param Client $client
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * Get the client.
     *
     * @return Client
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Retrieve the index name.
     *
     * @param string $locale
     *
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
