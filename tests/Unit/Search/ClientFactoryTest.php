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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Search;

use JoliCode\Elastically\Factory;
use JoliCode\Elastically\Mapping\MappingProviderInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\Documentable;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\ClientFactory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ClientFactoryTest extends TestCase
{
    public function testIndexNamesAppendLowercaseLocalesWithoutDroppingTheBaseCode(): void
    {
        $factory = new ClientFactory($this->createStub(SerializerInterface::class));
        $documentable = $this->documentable('monsieurbiz_product');

        self::assertSame('monsieurbiz_product', $factory->getIndexName($documentable, null));
        self::assertSame('monsieurbiz_product_en_us', $factory->getIndexName($documentable, 'EN_US'));
    }

    public function testFactoryConfigKeepsCallerOptionsAndAddsDocumentMappingSerializerAndNonBlankPrefix(): void
    {
        $serializer = $this->createStub(SerializerInterface::class);
        $documentable = $this->documentable('monsieurbiz_product');
        $documentable->setPrefix('  shop_  ');
        $baseConfig = ['connections' => ['default' => ['host' => '127.0.0.1']]];
        $factory = new ClientFactory($serializer, $baseConfig);

        $config = $this->readConfig($factory, $documentable, 'fr_FR');

        self::assertSame($baseConfig['connections'], $config['connections']);
        self::assertSame(['monsieurbiz_product_fr_fr' => ProductDTO::class], $config[Factory::CONFIG_INDEX_CLASS_MAPPING]);
        self::assertSame($documentable->getMappingProvider(), $config[Factory::CONFIG_MAPPINGS_PROVIDER]);
        self::assertSame($serializer, $config[Factory::CONFIG_SERIALIZER]);
        self::assertSame('shop_', $config[Factory::CONFIG_INDEX_PREFIX]);
    }

    public function testBlankPrefixIsNotSentToElastically(): void
    {
        $documentable = $this->documentable('monsieurbiz_product');
        $documentable->setPrefix('   ');

        $config = $this->readConfig(new ClientFactory($this->createStub(SerializerInterface::class)), $documentable, null);

        self::assertArrayNotHasKey(Factory::CONFIG_INDEX_PREFIX, $config);
    }

    private function documentable(string $code): Documentable
    {
        $documentable = new Documentable($code, ProductInterface::class, ProductDTO::class, [], []);
        $documentable->setMappingProvider($this->createStub(MappingProviderInterface::class));

        return $documentable;
    }

    private function readConfig(ClientFactory $factory, Documentable $documentable, ?string $locale): array
    {
        $method = new ReflectionMethod(ClientFactory::class, 'getConfig');

        return $method->invoke($factory, $documentable, $locale);
    }
}
