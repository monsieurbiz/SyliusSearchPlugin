<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Index;

use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Document;
use Jane\Component\AutoMapper\AutoMapperInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\ClientFactory;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class Indexer
{
    private ServiceRegistryInterface $documentableRegistry;

    private RepositoryInterface $localeRepository;

    private array $locales = [];

    private EntityManagerInterface $entityManager;

    private AutoMapperInterface $autoMapper;

    private ClientFactory $clientFactory;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        RepositoryInterface $localeRepository,
        EntityManagerInterface $entityManager,
        AutoMapperInterface $autoMapper,
        ClientFactory $clientFactory
    ) {
        $this->documentableRegistry = $documentableRegistry;
        $this->localeRepository = $localeRepository;
        $this->entityManager = $entityManager;
        $this->autoMapper = $autoMapper;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Retrieve all available locales.
     */
    public function getLocales(): array
    {
        if (0 === \count($this->locales)) {
            $locales = $this->localeRepository->findAll();
            $this->locales = array_filter(array_map(
                function (LocaleInterface $locale): string {
                    return $locale->getCode() ?? '';
                },
                $locales
            ));
        }

        return $this->locales;
    }

    /**
     * Index all documentable object.
     */
    public function indexAll(): void
    {
        /** @var DocumentableInterface $documentable */
        foreach ($this->documentableRegistry->all() as $documentable) {
            $this->indexDocumentable($documentable);
        }
    }

    public function indexByDocuments(DocumentableInterface $documentable, array $documents, ?string $locale = null, ?\JoliCode\Elastically\Indexer $indexer = null): void
    {
        if (null === $indexer) {
            $indexer = $this->clientFactory->getIndexer($documentable, $locale);
        }

        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $this->indexByDocuments($documentable, $documents, $localeCode, $indexer);
            }

            $indexer->flush();

            return;
        }
        $indexName = $this->getIndexName($documentable, $locale);
        foreach ($documents as $document) {
            if (null !== $locale && $document instanceof TranslatableInterface) {
                $document->setCurrentLocale($locale);
            }
            $dto = $this->autoMapper->map($document, $documentable->getTargetClass());
            // @phpstan-ignore-next-line
            $indexer->scheduleIndex($indexName, new Document((string) $document->getId(), $dto));
        }
    }

    public function deleteByDocuments(DocumentableInterface $documentable, array $documents, ?string $locale = null, ?\JoliCode\Elastically\Indexer $indexer = null): void
    {
        if (null === $indexer) {
            $indexer = $this->clientFactory->getIndexer($documentable, $locale);
        }

        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $this->deleteByDocuments($documentable, $documents, $localeCode, $indexer);
            }

            $indexer->flush();

            return;
        }

        $indexName = $this->getIndexName($documentable, $locale);
        foreach ($documents as $document) {
            if (null !== $locale && $document instanceof TranslatableInterface) {
                $document->setCurrentLocale($locale);
            }
            $indexer->scheduleDelete($indexName, (string) $document->getId());
        }
    }

    private function indexDocumentable(DocumentableInterface $documentable, ?string $locale = null): void
    {
        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $this->indexDocumentable($documentable, $localeCode);
            }

            return;
        }
        $indexName = $this->clientFactory->getIndexName($documentable, $locale);
        $indexBuilder = $this->clientFactory->getIndexBuilder($documentable, $locale);
        $newIndex = $indexBuilder->createIndex($indexName, [
            'index_code' => $documentable->getIndexCode(),
            'locale' => null !== $locale ? strtolower($locale) : null,
        ]);

        $indexer = $this->clientFactory->getIndexer($documentable, $locale);
        foreach ($documentable->getDatasource()->getItems($documentable->getSourceClass()) as $item) {
            $item = $this->getRealEntity($item);
            if (null !== $locale && $item instanceof TranslatableInterface) {
                $item->setCurrentLocale($locale);
            }
            $dto = $this->autoMapper->map($item, $documentable->getTargetClass());
            // @phpstan-ignore-next-line
            $indexer->scheduleIndex($newIndex, new Document((string) $item->getId(), $dto));
        }
        $indexer->flush();

        $indexBuilder->markAsLive($newIndex, $indexName);
        $indexBuilder->speedUpRefresh($newIndex);
        $indexBuilder->purgeOldIndices($indexName);
    }

    private function getIndexName(DocumentableInterface $documentable, ?string $locale = null): string
    {
        return $documentable->getIndexCode() . strtolower(null !== $locale ? '_' . $locale : '');
    }

    /**
     * Convert proxies classes to the entity one.
     *
     * This avoid to retrieve the incorrect Mapper and have errors like :
     * `index: /<INDEX_NAME>/_doc/<ID> caused failed to parse`
     *
     * @param mixed $entity
     *
     * @return mixed
     */
    private function getRealEntity($entity)
    {
        if (!$entity instanceof Proxy || !method_exists($entity, 'getId')) {
            return $entity;
        }

        // Clear the entity manager to detach the proxy object
        $this->entityManager->clear(\get_class($entity));
        // Retrieve the original class name
        $entityClassName = $this->entityManager->getClassMetadata(\get_class($entity))->rootEntityName;
        // Find the object in repository from the ID
        return $this->entityManager->find($entityClassName, $entity->getId());
    }
}
