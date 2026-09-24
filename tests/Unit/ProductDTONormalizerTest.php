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

use MonsieurBiz\SyliusSearchPlugin\AutoMapper\Configuration;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model;
use MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer\JaneObjectNormalizer;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Normalizer\Product\ProductDTONormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ProductDTONormalizerTest extends TestCase
{
    public function testExistingIndexedDocumentHydratesNestedDtosAndKeepsCustomFields(): void
    {
        $configuration = new Configuration(['targets' => [
            'product' => ProductDTO::class,
            'taxon' => Model\TaxonDTO::class,
            'product_taxon' => Model\ProductTaxonDTO::class,
            'image' => Model\ImageDTO::class,
            'channel' => Model\ChannelDTO::class,
            'product_attribute' => Model\ProductAttributeDTO::class,
            'pricing' => Model\PricingDTO::class,
        ]]);
        $objectNormalizer = new ObjectNormalizer();
        $serializer = new Serializer([
            new ProductDTONormalizer($configuration, $objectNormalizer),
            new JaneObjectNormalizer(),
            new JsonSerializableNormalizer(),
            $objectNormalizer,
        ], [new JsonEncoder()]);
        $document = [
            'id' => 42, 'code' => 'shirt', 'name' => 'Shirt', 'enabled' => true,
            'short_description' => 'Consumer field',
            'main_taxon' => ['code' => 'clothes', 'name' => 'Clothes'],
            'product_taxons' => [['taxon' => ['code' => 'clothes'], 'position' => 1]],
            'images' => [['path' => 'product.jpg', 'type' => 'thumbnail']],
            'channels' => [['code' => 'WEB']],
            'attributes' => ['material' => ['code' => 'material', 'value' => ['Cotton']]],
            'prices' => [['channel_code' => 'WEB', 'price' => 1234, 'price_reduced' => false]],
        ];
        $dto = $serializer->denormalize($document, ProductDTO::class, 'json');

        self::assertSame(42, $dto->getId());
        self::assertSame('Consumer field', $dto->getShortDescription());
        self::assertInstanceOf(Model\TaxonDTO::class, $dto->getMainTaxon());
        self::assertSame('clothes', $dto->getMainTaxon()->getCode());
        self::assertSame(1, $dto->getProductTaxons()[0]->getPosition());
        self::assertSame('product.jpg', $dto->getImages()[0]->getPath());
        self::assertSame('WEB', $dto->getChannels()[0]->getCode());
        self::assertSame(['Cotton'], $dto->getAttributes()['material']->getValue());
        self::assertSame(1234, $dto->getPrices()[0]->getPrice());
        self::assertJsonStringEqualsJsonString(json_encode($document, \JSON_THROW_ON_ERROR), $serializer->serialize($dto, 'json'));
    }
}
