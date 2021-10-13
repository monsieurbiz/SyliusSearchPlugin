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

namespace MonsieurBiz\SyliusSearchPlugin\Index;

use Doctrine\ORM\EntityManagerInterface;
use Elastica\Document;
use Jane\Component\AutoMapper\AutoMapperInterface;
use JoliCode\Elastically\Factory;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class Indexer
{
    private ServiceRegistryInterface $documentableRegistry;
    private RepositoryInterface $localeRepository;
    private array $locales = [];
    private EntityManagerInterface $entityManager;
    private AutoMapperInterface $autoMapper;
    private SerializerInterface $serializer;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        RepositoryInterface $localeRepository,
        EntityManagerInterface $entityManager,
        AutoMapperInterface $autoMapper,
        SerializerInterface $serializer
    ) {
        $this->documentableRegistry = $documentableRegistry;
        $this->localeRepository = $localeRepository;
        $this->entityManager = $entityManager;
        $this->autoMapper = $autoMapper;
        $this->serializer = $serializer;
    }

    /**
     * Retrieve all available locales.
     */
    public function getLocales(): array
    {
        if (empty($this->locales)) {
            $locales = $this->localeRepository->findAll();
            $this->locales = array_filter(array_map(
                function(LocaleInterface $locale): string {
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

    private function indexDocumentable(DocumentableInterface $documentable, ?string $locale = null): void
    {
        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $this->indexDocumentable($documentable, $localeCode);
            }

            return;
        }
        $indexName = $this->getIndexName($documentable, $locale);
        $factory = new Factory([
            Factory::CONFIG_MAPPINGS_PROVIDER => $documentable->getMappingProvider(),
            Factory::CONFIG_SERIALIZER => $this->serializer,
        ]);
        $indexBuilder = $factory->buildIndexBuilder();
        $newIndex = $indexBuilder->createIndex($indexName, [
            'index_code' => $documentable->getIndexCode(),
            'locale' => $locale ? strtolower($locale) : null,
        ]);

        $indexer = $factory->buildIndexer();
        $test = $documentable->getDatasource()->getItems($documentable->getSourceClass());
        foreach ($test as $item) {
            if (null !== $locale && $item instanceof TranslatableInterface) {
                $item->setCurrentLocale($locale);
            }
            $dto = $this->autoMapper->map($item, $documentable->getTargetClass());
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
}
