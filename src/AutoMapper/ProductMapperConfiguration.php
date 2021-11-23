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
use MonsieurBiz\SyliusSearchPlugin\Model\Product\VariantDTO;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;

final class ProductMapperConfiguration implements MapperConfigurationInterface
{
    private Configuration $configuration;
    private AutoMapperInterface $autoMapper;

    public function __construct(Configuration $configuration, AutoMapperInterface $autoMapper)
    {
        $this->configuration = $configuration;
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
                $images[] = $this->autoMapper->map($image, Image::class); // rename the target class to DTO
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
            $attributes = [];
            $currentLocale = $product->getTranslation()->getLocale(); // TODO default locale if it's null?
            foreach ($product->getAttributesByLocale($currentLocale, $currentLocale) as $attributeValue) {
                if (null === $attributeValue->getName() || null === $attributeValue->getValue()) {
                    continue;
                }
                $attribute = $attributeValue->getAttribute();
                if (!$attribute instanceof SearchableInterface || (!$attribute->isSearchable() && !$attribute->isFilterable())) {
                    continue;
                }
                /** @var ProductAttribute $attributeDTO */
                $attributeDTO = $this->autoMapper->map($attributeValue, ProductAttribute::class);
                // todo the getValue depends on the type ...
                $attributeDTO->setValue($attributeValue->getValue()); // we can't use the automapper for the value because it has a mixed type
                $attributes[$attributeValue->getCode()] = $attributeDTO;
            }

            return $attributes;
        });

        $metadata->forMember('variants', function(ProductInterface $product): array {
            $variants = [];
            foreach ($product->getEnabledVariants() as $variant) {
                $variants[] = $this->autoMapper->map($variant, VariantDTO::class);
            }

            return $variants;
        });
    }

    public function getSource(): string
    {
        return $this->configuration->getSourceClass('product');
    }

    public function getTarget(): string
    {
        return ProductDTO::class;
    }
}
