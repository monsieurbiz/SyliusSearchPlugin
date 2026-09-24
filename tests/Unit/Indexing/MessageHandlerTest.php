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

use MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use MonsieurBiz\SyliusSearchPlugin\MessageHandler\ProductReindexFromIdsHandler;
use MonsieurBiz\SyliusSearchPlugin\MessageHandler\ProductReindexFromTaxonHandler;
use MonsieurBiz\SyliusSearchPlugin\MessageHandler\ProductToDeleteFromIdsHandler;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistry;

final class MessageHandlerTest extends SearchTestCase
{
    #[DataProvider('repositoryResults')]
    public function testReindexUsesTheCurrentRepositoryResultIncludingMissingProducts(array $products): void
    {
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects(self::once())->method('findBy')->with(['id' => [1, 2]])->willReturn($products);
        $indexer = $this->createMock(IndexerInterface::class);
        $document = $this->document();
        $indexer->expects(self::once())->method('indexByDocuments')->with($document, $products);
        $registry = new ServiceRegistry(DocumentableInterface::class);
        $registry->register('search.documentable.monsieurbiz_product', $document);
        (new ProductReindexFromIdsHandler($repository, $indexer, $registry))(new ProductReindexFromIds([1, 2]));
    }

    public static function repositoryResults(): iterable
    {
        yield 'all deleted meanwhile' => [[]];
        yield 'one remaining product' => [[new Product()]];
        yield 'both products' => [[new Product(), new Product()]];
    }

    public function testDeleteUsesIdsWithoutReloadingRemovedEntities(): void
    {
        $document = $this->document();
        $registry = new ServiceRegistry(DocumentableInterface::class);
        $registry->register('search.documentable.monsieurbiz_product', $document);
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects(self::once())->method('deleteByDocumentIds')->with($document, [1, 2]);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        (new ProductToDeleteFromIdsHandler($indexer, $registry, $logger))(new ProductToDeleteFromIds([1, 2, 1]));
    }

    public function testDeleteTransportExceptionIsLoggedUnderTheExistingBestEffortContract(): void
    {
        $registry = new ServiceRegistry(DocumentableInterface::class);
        $registry->register('search.documentable.monsieurbiz_product', $this->document());
        $failure = new RuntimeException('Elasticsearch unavailable');
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('deleteByDocumentIds')->willThrowException($failure);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with('An error occurred while deleting products from search index', ['exception' => $failure]);
        (new ProductToDeleteFromIdsHandler($indexer, $registry, $logger))(new ProductToDeleteFromIds([1]));
    }

    public function testReindexFailuresPropagateForMessengerRetries(): void
    {
        $registry = new ServiceRegistry(DocumentableInterface::class);
        $registry->register('search.documentable.monsieurbiz_product', $this->document());
        $repository = $this->createStub(ProductRepositoryInterface::class);
        $repository->method('findBy')->willReturn([new Product()]);
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('indexByDocuments')->willThrowException(new RuntimeException('Elasticsearch unavailable'));
        $this->expectException(RuntimeException::class);
        (new ProductReindexFromIdsHandler($repository, $indexer, $registry))(new ProductReindexFromIds([1]));
    }

    public function testUnsupportedTaxonRepositoryDoesNotIndexUnrelatedProducts(): void
    {
        $registry = new ServiceRegistry(DocumentableInterface::class);
        $registry->register('search.documentable.monsieurbiz_product', $this->document());
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects(self::never())->method('indexByDocuments');
        (new ProductReindexFromTaxonHandler($this->createStub(ProductRepositoryInterface::class), $indexer, $registry))(new ProductReindexFromTaxon(1));
    }

    public function testMissingDocumentRegistrationIsNotSwallowedAsATransportFailure(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $this->expectException(\Sylius\Component\Registry\NonExistingServiceException::class);
        (new ProductToDeleteFromIdsHandler($this->createStub(IndexerInterface::class), new ServiceRegistry(DocumentableInterface::class), $logger))(new ProductToDeleteFromIds([1]));
    }
}
