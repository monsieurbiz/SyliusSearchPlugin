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

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper;

use App\Entity\Product\Product;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\AutoMapperInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Channel;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Image;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductAttribute;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxon;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Taxon;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Product\Model\ProductAttributeValue;

final class ProductMapperConfiguration implements MapperConfigurationInterface
{
    private AutoMapperInterface $autoMapper;

    public function __construct(AutoMapperInterface $autoMapper)
    {
        $this->autoMapper = $autoMapper;
    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        if (!$metadata instanceof MapperMetadata) {
            return;
        }

        $metadata->forMember('id', function(ProductInterface $product): int {
            return $product->getId();
        });

        $metadata->forMember('code', function(ProductInterface $product): ?string {
            return $product->getCode();
        });

        $metadata->forMember('enabled', function(ProductInterface $product): bool {
            return $product->isEnabled();
        });

        $metadata->forMember('slug', function(ProductInterface $product): ?string {
            return $product->getSlug();
        });

        $metadata->forMember('name', function(ProductInterface $product): ?string {
            return $product->getName();
        });

        $metadata->forMember('description', function(ProductInterface $product): ?string {
            return $product->getDescription();
        });

        $metadata->forMember('images', function(ProductInterface $product): array {
            $images = [];
            foreach ($product->getImages() as $image) {
                $images[] = $this->autoMapper->map($image, Image::class);
            }

            return $images;
        });

        $metadata->forMember('mainTaxon', function(ProductInterface $product): Taxon {
            return $this->autoMapper->map($product->getMainTaxon(), Taxon::class);
        });

        $metadata->forMember('product_taxons', function(ProductInterface $product): array {
            return array_map(function(ProductTaxonInterface $productTaxon) {
                return $this->autoMapper->map($productTaxon, ProductTaxon::class);
            }, $product->getProductTaxons()->toArray());
        });

        $metadata->forMember('channels', function(ProductInterface $product): array {
            return array_map(function(ChannelInterface $channel) {
                return $this->autoMapper->map($channel, Channel::class);
            }, $product->getChannels()->toArray());
        });

        $metadata->forMember('attributes', function(ProductInterface $product): array {
            $currentLocale = $product->getTranslation()->getLocale();
            /** @var ProductAttributeValue $attributeValue */
            foreach ($product->getAttributesByLocale($currentLocale, $currentLocale) as $attributeValue) {
                if (null === $attributeValue->getName() || null === $attributeValue->getValue()) {
                    continue;
                }
                $attribute = $attributeValue->getAttribute();
                if (!$attribute instanceof SearchableInterface || (!$attribute->isSearchable() && !$attribute->isFilterable())) {
                    continue;
                }
                $attributeDTO = $this->autoMapper->map($attributeValue, ProductAttribute::class);
                $attributeDTO->setValue($attributeValue->getValue()); // we can't use the automapper for the value because it has a mixed type
                $attributes[$attributeValue->getCode()] = $attributeDTO;
            }

            return $attributes;
        });
    }

    public function getSource(): string
    {
        return Product::class;
    }

    public function getTarget(): string
    {
        return ProductDTO::class;
    }
}
