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

use Elastica\Document;
use Elastica\Exception\ResponseException;
use JoliCode\Elastically\Client;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingParamException;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadOnlyIndexException;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Result;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Provider\DocumentRepositoryProvider;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchQueryProvider;
use Psr\Log\LoggerInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

class Indexer extends AbstractIndex
{
    /**
     * @var DocumentRepositoryProvider
     */
    protected $documentRepositoryProvider;

    /** @var RepositoryInterface */
    private $localeRepository;

    /** @var array */
    private $locales = [];

    /**
     * PopulateCommand constructor.
     *
     * @param Client $client
     * @param DocumentRepositoryProvider $documentRepositoryProvider
     * @param RepositoryInterface $localeRepository
     * @param SearchQueryProvider $searchQueryProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        DocumentRepositoryProvider $documentRepositoryProvider,
        RepositoryInterface $localeRepository
    ) {
        parent::__construct($client);
        $this->documentRepositoryProvider = $documentRepositoryProvider;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Retrieve all available locales.
     *
     * @return array
     */
    public function getLocales(): array
    {
        if (empty($this->locales)) {
            $locales = $this->localeRepository->findAll();
            $this->locales = array_map(
                function(LocaleInterface $locale) {
                    return $locale->getCode();
                },
                $locales
            );
        }

        return $this->locales;
    }

    /**
     * Index all documents in all locales.
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
     * Index all document for a locale.
     *
     * @param string $locale
     *
     * @throws \Exception
     */
    public function indexAllByLocale(string $locale): void
    {
        $indexName = $this->getIndexName($locale);
        $newIndex = $this->getIndexBuilder()->createIndex($indexName);
        $this->getIndexBuilder()->markAsLive(
            $newIndex,
            $indexName
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

        $this->getIndexer()->refresh($indexName);
        try {
            $this->getIndexBuilder()->purgeOldIndices($indexName);
        } catch (ResponseException $exception) {
            throw new ReadOnlyIndexException($exception->getMessage());
        }
    }

    /**
     * Index a document for all locales.
     *
     * @param DocumentableInterface $subject
     *
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
     * Index a document for one locale.
     *
     * @param Result $document
     * @param string $locale
     *
     * @throws MissingParamException
     */
    public function indexOneByLocale(Result $document, string $locale): void
    {
        $this->getIndexer()->scheduleIndex(
            $this->getClient()->getIndex($this->getIndexName($locale)),
            new Document($document->getUniqId(), $document)
        );
    }

    /**
     * Remove a document for all locales.
     *
     * @param DocumentableInterface $subject
     *
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
     * Remove a document for all locales.
     *
     * @param Result $document
     * @param string $locale
     *
     * @throws MissingParamException
     */
    public function removeOneByLocale(Result $document, string $locale): void
    {
        $this->getIndexer()->scheduleDelete(
            $this->getClient()->getIndex($this->getIndexName($locale)),
            $document->getUniqId()
        );
    }
}
