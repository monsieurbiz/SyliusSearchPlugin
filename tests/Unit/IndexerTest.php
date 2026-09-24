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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit;

use Doctrine\ORM\EntityManagerInterface;
use JoliCode\Elastically\Index;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer as ElasticallyIndexer;
use MonsieurBiz\SyliusSearchPlugin\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Mapper\DocumentMapperInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\DatasourceInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\ClientFactory;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Registry\ServiceRegistryInterface;
use TypeError;

final class IndexerTest extends TestCase
{
    public function testFailedMappingDoesNotPublishOrPurgeIndexes(): void
    {
        $source = $this->createMock(DatasourceInterface::class);
        $source->method('getItems')->willReturn([new Product()]);
        $document = $this->createMock(DocumentableInterface::class);
        $document->method('getDatasource')->willReturn($source);
        $document->method('getSourceClass')->willReturn(Product::class);
        $document->method('getTargetClass')->willReturn(ProductDTO::class);
        $document->method('getIndexCode')->willReturn('products');
        $document->method('isTranslatable')->willReturn(false);
        $registry = $this->createMock(ServiceRegistryInterface::class);
        $registry->method('all')->willReturn([$document]);
        $builder = $this->createMock(IndexBuilder::class);
        $builder->method('createIndex')->willReturn($this->createMock(Index::class));
        $builder->expects(self::never())->method('markAsLive');
        $builder->expects(self::never())->method('purgeOldIndices');
        $client = $this->createMock(ClientFactory::class);
        $client->method('getIndexName')->willReturn('products');
        $client->method('getIndexBuilder')->willReturn($builder);
        $client->method('getIndexer')->willReturn($this->createMock(ElasticallyIndexer::class));
        $mapper = $this->createMock(DocumentMapperInterface::class);
        $mapper->method('map')->willThrowException(new TypeError('Cannot map product'));
        $indexer = new Indexer($registry, $this->createMock(ChannelRepositoryInterface::class), $this->createMock(EntityManagerInterface::class), $mapper, $client);

        $this->expectException(TypeError::class);
        $indexer->indexAll();
    }
}
