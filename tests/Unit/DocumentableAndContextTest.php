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

use JoliCode\Elastically\Mapping\MappingProviderInterface;
use MonsieurBiz\SyliusSearchPlugin\Context\ChannelSimulationContext;
use MonsieurBiz\SyliusSearchPlugin\Manager\AutomaticProductReindexManager;
use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\DatasourceInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\Documentable;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ProductInterface;

final class DocumentableAndContextTest extends TestCase
{
    public function testDocumentableExposesTemplatesLimitsCollaboratorsAndTranslatability(): void
    {
        $datasource = $this->createStub(DatasourceInterface::class);
        $mappingProvider = $this->createStub(MappingProviderInterface::class);
        $documentable = new Documentable('monsieurbiz_product', ProductInterface::class, ProductDTO::class, ['item' => 'item.html.twig'], ['search' => [9]]);
        $documentable->setDatasource($datasource);
        $documentable->setMappingProvider($mappingProvider);

        self::assertSame('monsieurbiz_product', $documentable->getIndexCode());
        self::assertSame(ProductInterface::class, $documentable->getSourceClass());
        self::assertSame(ProductDTO::class, $documentable->getTargetClass());
        self::assertTrue($documentable->isTranslatable());
        self::assertSame('item.html.twig', $documentable->getTemplate('item'));
        self::assertNull($documentable->getTemplate('missing'));
        self::assertSame(['search' => [9]], $documentable->getLimits());
        self::assertSame([9], $documentable->getLimits('search'));
        self::assertSame([], $documentable->getLimits('instant_search'));
        self::assertSame($datasource, $documentable->getDatasource());
        self::assertSame($mappingProvider, $documentable->getMappingProvider());
    }

    public function testDocumentableRejectsInvalidTargetClassBeforeClientConfigurationUsesIt(): void
    {
        $documentable = new Documentable('broken', ProductInterface::class, 'App\\MissingDocument', [], []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid document target class: App\\MissingDocument');

        $documentable->getTargetClass();
    }

    public function testChannelSimulationRequiresAnExplicitChannel(): void
    {
        $context = new ChannelSimulationContext();

        $this->expectException(ChannelNotFoundException::class);
        $context->getChannel();
    }

    public function testChannelSimulationCanBeSetAndCleared(): void
    {
        $context = new ChannelSimulationContext();
        $channel = new Channel();

        $context->setChannel($channel);
        self::assertSame($channel, $context->getChannel());

        $context->setChannel(null);
        $this->expectException(ChannelNotFoundException::class);
        $context->getChannel();
    }

    public function testAutomaticProductReindexManagerDefaultsToEnabledAndCanBeDisabledTemporarily(): void
    {
        $manager = new AutomaticProductReindexManager();

        self::assertTrue($manager->shouldBeAutomaticallyReindex());
        $manager->shouldAutomaticallyReindex(false);
        self::assertFalse($manager->shouldBeAutomaticallyReindex());
        $manager->shouldAutomaticallyReindex(true);
        self::assertTrue($manager->shouldBeAutomaticallyReindex());
    }
}
