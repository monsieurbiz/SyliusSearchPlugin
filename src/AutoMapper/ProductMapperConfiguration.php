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
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProductMapperConfiguration implements MapperConfigurationInterface
{
    private Configuration $configuration;
    private AutoMapperInterface $autoMapper;
    private ProductVariantResolverInterface $productVariantResolver;
    private RequestStack $requestStack;

    public function __construct(
        Configuration $configuration,
        AutoMapperInterface $autoMapper,
        ProductVariantResolverInterface $productVariantResolver,
        RequestStack $requestStack
    ) {
        $this->configuration = $configuration;
        $this->autoMapper = $autoMapper;
        // todo change the resolver from the configuration
        $this->productVariantResolver = $productVariantResolver;
        $this->requestStack = $requestStack;
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

        $metadata->forMember('created_at', function(ProductInterface $product) {
            return $product->getCreatedAt();
        });

        $metadata->forMember('images', function(ProductInterface $product): array {
            $images = [];
            $imageDTOClass = $this->configuration->getTargetClass('image');
            foreach ($product->getImages() as $image) {
                $images[] = $this->autoMapper->map($image, $imageDTOClass);
            }

            return $images;
        });

        $metadata->forMember('mainTaxon', function(ProductInterface $product) {
            return $product->getMainTaxon() ? $this->autoMapper->map($product->getMainTaxon(), $this->configuration->getTargetClass('taxon')) : null;
        });

        $metadata->forMember('product_taxons', function(ProductInterface $product): array {
            return array_map(function(ProductTaxonInterface $productTaxon) {
                // todo add parent taxon in Taxon object with automapper
                return $this->autoMapper->map($productTaxon, $this->configuration->getTargetClass('product_taxon'));
            }, $product->getProductTaxons()->toArray());
        });

        $metadata->forMember('channels', function(ProductInterface $product): array {
            return array_map(function(ChannelInterface $channel) {
                return $this->autoMapper->map($channel, $this->configuration->getTargetClass('channel'));
            }, $product->getChannels()->toArray());
        });

        $metadata->forMember('attributes', function(ProductInterface $product): array {
            $attributes = [];
            $currentLocale = $product->getTranslation()->getLocale();
            $productAttributeDTOClass = $this->configuration->getTargetClass('product_attribute');
            foreach ($product->getAttributesByLocale($currentLocale, $currentLocale) as $attributeValue) {
                if (null === $attributeValue->getName() || null === $attributeValue->getValue()) {
                    continue;
                }
                $attribute = $attributeValue->getAttribute();
                if (!$attribute instanceof SearchableInterface || (!$attribute->isSearchable() && !$attribute->isFilterable())) {
                    continue;
                }
                $attributes[$attributeValue->getCode()] = $this->autoMapper->map($attributeValue, $productAttributeDTOClass);
            }

            return $attributes;
        });

        $metadata->forMember('variants', function(ProductInterface $product): array {
            $variants = [];
            $productVariantDTOClass = $this->configuration->getTargetClass('product_variant');
            foreach ($product->getEnabledVariants() as $variant) {
                $variants[] = $this->autoMapper->map($variant, $productVariantDTOClass);
            }

            return $variants;
        });

        $metadata->forMember('prices', function(ProductInterface $product): array {
            $prices = [];
            foreach ($product->getChannels() as $channel) {
                $request = new Request(['_channel_code' => $channel->getCode()]);
                $this->requestStack->push($request);
                if (null === ($variant = $this->productVariantResolver->getVariant($product))) {
                    continue;
                }
                $this->requestStack->pop();
                $prices[] = $this->autoMapper->map(
                    $variant->getChannelPricingForChannel($channel),
                    $this->configuration->getTargetClass('pricing')
                );
            }

            return $prices;
        });
    }

    public function getSource(): string
    {
        return $this->configuration->getSourceClass('product');
    }

    public function getTarget(): string
    {
        return $this->configuration->getTargetClass('product');
    }
}
