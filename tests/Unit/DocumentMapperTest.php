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

use DateTimeImmutable;
use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\Configuration;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader\TextReader;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueResolver;
use MonsieurBiz\SyliusSearchPlugin\Context\ChannelSimulationContext;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model;
use MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer\JaneObjectNormalizer;
use MonsieurBiz\SyliusSearchPlugin\Mapper\DocumentMapper;
use MonsieurBiz\SyliusSearchPlugin\Mapper\NestedProductMapper;
use MonsieurBiz\SyliusSearchPlugin\Mapper\ProductMapper;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\SearchableTrait;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\VariantDTO;
use MonsieurBiz\SyliusSearchPlugin\Resolver\CheapestProductVariantResolver;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductImage;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeValue;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class DocumentMapperTest extends TestCase
{
    public function testPopulatedProductDocumentKeepsItsNestedFieldsAndChannelPrices(): void
    {
        $mapper = $this->mapper();
        $product = new Product();
        (new ReflectionProperty($product, 'id'))->setValue($product, 42);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setCode('SHIRT');
        $product->setName('Cotton shirt');
        $product->setSlug('cotton-shirt');
        $product->setDescription('A searchable shirt');
        $product->setCreatedAt(new DateTimeImmutable('2024-01-02 03:04:05'));
        $image = new ProductImage();
        $image->setPath('shirt.jpg');
        $image->setType('main');
        $product->addImage($image);
        $taxon = new Taxon();
        $taxon->setCurrentLocale('en_US');
        $taxon->setFallbackLocale('en_US');
        $taxon->setCode('shirts');
        $taxon->setName('Shirts');
        $taxon->setPosition(2);
        $taxon->setLevel(1);
        $product->setMainTaxon($taxon);
        $link = new ProductTaxon();
        $link->setTaxon($taxon);
        $link->setPosition(3);
        $product->addProductTaxon($link);
        foreach (['WEB_US', 'WEB_EU'] as $code) {
            $channel = new Channel();
            $channel->setCode($code);
            $product->addChannel($channel);
        }
        $product->addVariant($this->variant('S', ['WEB_US' => [1800, 2000], 'WEB_EU' => [900, null]]));
        $product->addVariant($this->variant('M', ['WEB_US' => [1200, 1300], 'WEB_EU' => [1900, null]]));
        $disabled = $this->variant('DISABLED', ['WEB_US' => [100, null]]);
        $disabled->disable();
        $product->addVariant($disabled);
        $attribute = new SearchableAttribute();
        $attribute->setCurrentLocale('en_US');
        $attribute->setFallbackLocale('en_US');
        $attribute->setCode('material');
        $attribute->setName('Material');
        $attribute->setType('text');
        $attribute->setStorageType('text');
        $attribute->setSearchable(true);
        $value = new ProductAttributeValue();
        $value->setAttribute($attribute);
        $value->setLocaleCode('en_US');
        $value->setValue('Cotton');
        $product->addAttribute($value);

        $dto = $mapper->map($product, ProductDTO::class);
        self::assertSame(42, $dto->getId());
        self::assertSame('shirt.jpg', $dto->getImages()[0]->getPath());
        self::assertSame('Shirts', $dto->getMainTaxon()->getName());
        self::assertSame(3, $dto->getProductTaxons()[0]->getPosition());
        self::assertSame('WEB_US', $dto->getChannels()[0]->getCode());
        self::assertSame('Cotton', $dto->getAttributes()['material']->getValue());
        self::assertCount(2, $dto->getVariants());
        self::assertTrue($dto->getVariants()[0]->getEnabled());
        self::assertTrue($dto->getVariants()[0]->isInStock());
        self::assertSame(1200, $dto->getPrices()[0]->getPrice());
        self::assertSame(1300, $dto->getPrices()[0]->getOriginalPrice());
        self::assertTrue($dto->getPrices()[0]->getPriceReduced());
        self::assertSame(900, $dto->getPrices()[1]->getPrice());
        self::assertFalse($dto->getPrices()[1]->isInitialized('originalPrice'));
        $serializer = new Serializer([new JaneObjectNormalizer(), new JsonSerializableNormalizer(), new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])], [new JsonEncoder()]);
        $json = json_decode($serializer->serialize($dto, 'json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame(['path' => 'shirt.jpg', 'type' => 'main'], $json['images'][0]);
        self::assertSame(['name' => 'Shirts', 'code' => 'shirts', 'position' => 2, 'level' => 1], $json['main_taxon']);
        self::assertSame(['channel_code' => 'WEB_US', 'price' => 1200, 'original_price' => 1300, 'price_reduced' => true], $json['prices'][0]);
        self::assertSame(['code' => 'material', 'name' => 'Material', 'value' => 'Cotton'], $json['attributes']['material']);
        self::assertSame('2024-01-02 03:04:05', $json['created_at']);
    }

    public function testApiGroupsAreIgnoredBySearchWithoutChangingApiSerialization(): void
    {
        $channel = new GroupedSearchChannel();
        $channel->setCode('WEB');
        $dto = $this->mapper()->map($channel, Model\ChannelDTO::class);
        self::assertSame('WEB', $dto->getCode());
        $serializer = new Serializer([new ObjectNormalizer(new ClassMetadataFactory(new AttributeLoader()))]);
        self::assertSame([], $serializer->normalize($channel, null, ['groups' => ['unrelated']]));
        self::assertSame(['code' => 'WEB'], $serializer->normalize($channel, null, ['groups' => ['api:channel:read']]));
    }

    public function testConfiguredTargetSubclassIsRespected(): void
    {
        $channel = new Channel();
        $channel->setCode('CUSTOM');
        $dto = $this->mapper(CustomChannelDTO::class)->map($channel, CustomChannelDTO::class);
        self::assertInstanceOf(CustomChannelDTO::class, $dto);
        self::assertSame('CUSTOM', $dto->getCode());
    }

    public function testUnsupportedMappingFailsExplicitly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mapper()->map(new stdClass(), ProductDTO::class);
    }

    private function mapper(string $channelTarget = Model\ChannelDTO::class): DocumentMapper
    {
        $configuration = new Configuration([
            'sources' => ['product' => Product::class, 'product_variant' => ProductVariant::class, 'image' => ProductImage::class, 'taxon' => Taxon::class, 'product_taxon' => ProductTaxon::class, 'channel' => Channel::class, 'pricing' => ChannelPricing::class, 'product_attribute_value' => ProductAttributeValue::class],
            'targets' => ['product' => ProductDTO::class, 'product_variant' => VariantDTO::class, 'image' => Model\ImageDTO::class, 'taxon' => Model\TaxonDTO::class, 'product_taxon' => Model\ProductTaxonDTO::class, 'channel' => $channelTarget, 'pricing' => Model\PricingDTO::class, 'product_attribute' => Model\ProductAttributeDTO::class],
        ]);
        $availability = $this->createMock(AvailabilityCheckerInterface::class);
        $availability->method('isStockAvailable')->willReturn(true);
        $accessor = PropertyAccess::createPropertyAccessor();
        $nested = new NestedProductMapper($configuration, $accessor, $availability, new ProductAttributeValueResolver(['text' => new TextReader()]));
        $channelContext = new ChannelSimulationContext();
        $product = new ProductMapper($configuration, $nested, new CheapestProductVariantResolver($channelContext), $availability, $channelContext, $accessor);

        return new DocumentMapper([$product, $nested]);
    }

    private function variant(string $code, array $prices): ProductVariant
    {
        $variant = new ProductVariant();
        $variant->setCode($code);
        foreach ($prices as $channel => [$price, $originalPrice]) {
            $pricing = new ChannelPricing();
            $pricing->setChannelCode($channel);
            $pricing->setPrice($price);
            $pricing->setOriginalPrice($originalPrice);
            $variant->addChannelPricing($pricing);
        }

        return $variant;
    }
}

final class SearchableAttribute extends ProductAttribute implements SearchableInterface
{
    use SearchableTrait;
}

final class GroupedSearchChannel extends Channel
{
    #[Groups(['api:channel:read'])]
    public function getCode(): ?string
    {
        return parent::getCode();
    }
}

final class CustomChannelDTO extends Model\ChannelDTO
{
}
