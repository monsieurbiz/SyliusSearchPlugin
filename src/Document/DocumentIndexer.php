<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Document;

use Elastica\Exception\ResponseException;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingParamException;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadOnlyIndexException;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentResult;
use Elastica\Document;
use JoliCode\Elastically\Client;
use Psr\Log\LoggerInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Provider\SearchRequestProvider;
use MonsieurBiz\SyliusSearchPlugin\Provider\DocumentRepositoryProvider;
use Webmozart\Assert\Assert;


class DocumentIndexer extends AbstractDocumentIndex
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
     * @param Client $client
     * @param DocumentRepositoryProvider $documentRepositoryProvider
     * @param RepositoryInterface $localeRepository
     * @param SearchRequestProvider $searchRequestProvider
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
}
