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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Indexing;

use Doctrine\ORM\EntityManagerInterface;
use Elastica\Document;
use Elastica\Response;
use JoliCode\Elastically\Index;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer as BulkIndexer;
use MonsieurBiz\SyliusSearchPlugin\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Mapper\DocumentMapperInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\DatasourceInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\ClientFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Registry\ServiceRegistry;

final class IndexLifecycleTest extends TestCase
{
    #[DataProvider('lifecycleCases')]
    public function testRebuildOrderAndFailureBoundaries(?string $failure, array $expected): void
    {
        $events = [];
        $record = static function (string $phase) use (&$events, $failure): void {
            $events[] = $phase;
            if ($phase === $failure) {
                throw new RuntimeException('failure:' . $phase);
            }
        };
        $index = $this->createStub(Index::class);
        $builder = $this->createMock(IndexBuilder::class);
        $builder->method('createIndex')->willReturnCallback(static function ($name, $context) use ($index, $record) {
            self::assertSame('products', $name);
            self::assertSame(['index_code' => 'products', 'locale' => null], $context);
            $record('create');

            return $index;
        });
        $builder->method('markAsLive')->willReturnCallback(static function ($actual, $name) use ($index, $record) {
            self::assertSame($index, $actual);
            self::assertSame('products', $name);
            $record('publish');

            return new Response('{}', 200);
        });
        $builder->method('speedUpRefresh')->willReturnCallback(static fn () => $record('refresh'));
        $builder->method('purgeOldIndices')->willReturnCallback(static function () use ($record) {
            $record('purge');

            return [];
        });
        $dto = new ProductDTO(['id' => 42]);
        $mapper = $this->createMock(DocumentMapperInterface::class);
        $mapper->method('map')->willReturnCallback(static function () use ($record, $dto) {
            $record('map');

            return $dto;
        });
        $bulk = $this->createMock(BulkIndexer::class);
        $bulk->method('scheduleIndex')->willReturnCallback(static function ($actual, Document $document) use ($index, $dto, $record): void {
            self::assertSame($index, $actual);
            self::assertSame('42', $document->getId());
            self::assertSame($dto, $document->getData());
            $record('schedule');
        });
        $bulk->method('flush')->willReturnCallback(static fn () => $record('flush'));
        $factory = $this->createStub(ClientFactory::class);
        $factory->method('getIndexName')->willReturn('products');
        $factory->method('getIndexBuilder')->willReturn($builder);
        $factory->method('getIndexer')->willReturn($bulk);
        $source = $this->createStub(DatasourceInterface::class);
        $source->method('getItems')->willReturn([new Product()]);
        $document = $this->document(false);
        $document->method('getDatasource')->willReturn($source);
        $registry = new ServiceRegistry(DocumentableInterface::class);
        $registry->register('products', $document);
        $indexer = new Indexer($registry, $this->createStub(ChannelRepositoryInterface::class), $this->createStub(EntityManagerInterface::class), $mapper, $factory);

        try {
            $indexer->indexAll();
            self::assertNull($failure, 'The expected external failure was not propagated.');
        } catch (RuntimeException $exception) {
            self::assertSame('failure:' . $failure, $exception->getMessage());
        }
        self::assertSame($expected, $events);
    }

    public static function lifecycleCases(): iterable
    {
        $phases = ['create', 'map', 'schedule', 'flush', 'publish', 'refresh', 'purge'];
        yield 'success' => [null, $phases];
        foreach ($phases as $index => $phase) {
            yield 'failure at ' . $phase => [$phase, \array_slice($phases, 0, $index + 1)];
        }
    }

    public function testDeletionFansOutAcrossUniqueEnabledChannelLocalesAndCastsIds(): void
    {
        $events = [];
        $bulk = $this->createMock(BulkIndexer::class);
        $bulk->method('scheduleDelete')->willReturnCallback(static function ($index, $id) use (&$events): void { $events[] = ['delete', $id]; });
        $bulk->method('flush')->willReturnCallback(static function () use (&$events): void { $events[] = ['flush']; });
        $factory = $this->createMock(ClientFactory::class);
        $factory->expects(self::once())->method('getIndexer')->with(self::anything(), null)->willReturn($bulk);
        $index = $this->createStub(Index::class);
        $factory->method('getIndex')->willReturnCallback(static function ($document, $locale) use (&$events, $index) {
            $events[] = ['index', $locale];

            return $index;
        });
        $channels = $this->channels();
        $indexer = new Indexer(new ServiceRegistry(DocumentableInterface::class), $channels, $this->createStub(EntityManagerInterface::class), $this->createStub(DocumentMapperInterface::class), $factory);
        $indexer->deleteByDocumentIds($this->document(true), [1, '2']);
        self::assertSame([
            ['index', 'en_US'], ['delete', '1'], ['delete', '2'], ['flush'],
            ['index', 'fr_FR'], ['delete', '1'], ['delete', '2'], ['flush'],
        ], $events);
    }

    public function testIncrementalIndexingMapsEachLocaleBeforeScheduling(): void
    {
        $locales = [];
        $product = new Product();
        $product->setFallbackLocale('en_US');
        foreach (['en_US' => 'Shirt', 'fr_FR' => 'Chemise'] as $code => $name) {
            $translation = new \Sylius\Component\Core\Model\ProductTranslation();
            $translation->setLocale($code);
            $translation->setName($name);
            $product->addTranslation($translation);
        }
        $mapper = $this->createMock(DocumentMapperInterface::class);
        $mapper->expects(self::exactly(2))->method('map')->willReturnCallback(static function (Product $source) use (&$locales) {
            $locales[] = [$source->getTranslation()->getLocale(), $source->getName()];

            return new ProductDTO(['id' => 42]);
        });
        $bulk = $this->createMock(BulkIndexer::class);
        $bulk->expects(self::exactly(2))->method('scheduleIndex');
        $bulk->expects(self::exactly(2))->method('flush');
        $factory = $this->createMock(ClientFactory::class);
        $factory->expects(self::never())->method('getIndexer');
        $factory->method('getIndex')->willReturn($this->createStub(Index::class));
        $indexer = new Indexer(new ServiceRegistry(DocumentableInterface::class), $this->channels(), $this->createStub(EntityManagerInterface::class), $mapper, $factory);
        $indexer->indexByDocuments($this->document(true), [$product], null, $bulk);
        self::assertSame([['en_US', 'Shirt'], ['fr_FR', 'Chemise']], $locales);
    }

    public function testDeleteByDocumentsDoesNotNeedToMapObjects(): void
    {
        $product = new Product();
        (new ReflectionProperty($product, 'id'))->setValue($product, 42);
        $mapper = $this->createMock(DocumentMapperInterface::class);
        $mapper->expects(self::never())->method('map');
        $bulk = $this->createMock(BulkIndexer::class);
        $index = $this->createStub(Index::class);
        $bulk->expects(self::once())->method('scheduleDelete')->with($index, '42');
        $bulk->expects(self::once())->method('flush');
        $factory = $this->createStub(ClientFactory::class);
        $factory->method('getIndex')->willReturn($index);
        $indexer = new Indexer(new ServiceRegistry(DocumentableInterface::class), $this->createStub(ChannelRepositoryInterface::class), $this->createStub(EntityManagerInterface::class), $mapper, $factory);
        $indexer->deleteByDocuments($this->document(false), [$product], null, $bulk);
    }

    private function channels(): ChannelRepositoryInterface
    {
        $en = new Locale();
        $en->setCode('en_US');
        $fr = new Locale();
        $fr->setCode('fr_FR');
        $first = new Channel();
        $first->addLocale($en);
        $first->addLocale($fr);
        $second = new Channel();
        $second->addLocale($en);
        $repository = $this->createMock(ChannelRepositoryInterface::class);
        $repository->expects(self::once())->method('findBy')->with(['enabled' => true])->willReturn([$first, $second]);

        return $repository;
    }

    private function document(bool $translatable): DocumentableInterface
    {
        $document = $this->createStub(DocumentableInterface::class);
        $document->method('getIndexCode')->willReturn('products');
        $document->method('getSourceClass')->willReturn(Product::class);
        $document->method('getTargetClass')->willReturn(ProductDTO::class);
        $document->method('isTranslatable')->willReturn($translatable);

        return $document;
    }
}
