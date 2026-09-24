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

namespace MonsieurBiz\SyliusSearchPlugin\Mapper;

use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ConfigurationInterface;
use MonsieurBiz\SyliusSearchPlugin\Context\ChannelSimulationContext;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface as ModelProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductMapper implements DocumentMappingInterface
{
    private ConfigurationInterface $configuration;

    private DocumentMapperInterface $nestedMapper;

    private ProductVariantResolverInterface $productVariantResolver;

    private AvailabilityCheckerInterface $availabilityChecker;

    private ChannelSimulationContext $channelSimulationContext;

    public function __construct(
        ConfigurationInterface $configuration,
        DocumentMapperInterface $nestedMapper,
        ProductVariantResolverInterface $productVariantResolver,
        AvailabilityCheckerInterface $availabilityChecker,
        ChannelSimulationContext $channelSimulationContext,
        private PropertyAccessorInterface $propertyAccessor
    ) {
        $this->configuration = $configuration;
        $this->nestedMapper = $nestedMapper;
        $this->productVariantResolver = $productVariantResolver;
        $this->availabilityChecker = $availabilityChecker;
        $this->channelSimulationContext = $channelSimulationContext;
    }

    public function supports(object $source, string $targetClass): bool
    {
        return $source instanceof ProductInterface
            && is_a($source, $this->getSource())
            && $targetClass === $this->getTarget();
    }

    public function map(object $source, string $targetClass): object
    {
        if (!$source instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected a Sylius product.');
        }

        $values = [
            'id' => $source->getId(),
            'code' => $source->getCode(),
            'enabled' => $source->isEnabled(),
            'slug' => $source->getSlug(),
            'name' => $source->getName(),
            'description' => $source->getDescription(),
            'created_at' => $source->getCreatedAt(),
            'images' => $this->getImages($source),
            'mainTaxon' => $this->getMainTaxon($source),
            'product_taxons' => $this->getProductTaxons($source),
            'channels' => $this->getChannels($source),
            'attributes' => $this->getAttributes($source),
            'options' => $this->getOptions($source),
            'variants' => $this->getVariants($source),
            'prices' => $this->getPrices($source),
        ];
        $target = new $targetClass();
        foreach ($values as $property => $value) {
            $this->propertyAccessor->setValue($target, $property, $value);
        }

        return $target;
    }

    private function getImages(ProductInterface $product): array
    {
        $images = [];
        foreach ($product->getImages() as $image) {
            $images[] = $this->nestedMapper->map($image, $this->configuration->getTargetClass('image'));
        }

        return $images;
    }

    private function getMainTaxon(ProductInterface $product): mixed
    {
        $taxon = $product->getMainTaxon();
        if (null === $taxon) {
            return null;
        }
        $locale = $product->getTranslation()->getLocale();
        if (null !== $locale) {
            $taxon->setCurrentLocale($locale);
        }

        return $this->nestedMapper->map($taxon, $this->configuration->getTargetClass('taxon'));
    }

    private function getProductTaxons(ProductInterface $product): array
    {
        return array_map(function (ProductTaxonInterface $productTaxon) use ($product) {
            $taxon = $productTaxon->getTaxon();
            $locale = $product->getTranslation()->getLocale();
            if (null !== $locale && null !== $taxon) {
                $taxon->setCurrentLocale($locale);
            }

            return $this->nestedMapper->map($productTaxon, $this->configuration->getTargetClass('product_taxon'));
        }, $product->getProductTaxons()->toArray());
    }

    private function getChannels(ProductInterface $product): array
    {
        return array_map(function ($channel) {
            return $this->nestedMapper->map($channel, $this->configuration->getTargetClass('channel'));
        }, $product->getChannels()->toArray());
    }

    public function getSource(): string
    {
        return $this->configuration->getSourceClass('product');
    }

    public function getTarget(): string
    {
        return $this->configuration->getTargetClass('product');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttributes(ProductInterface $product): array
    {
        $attributes = [];
        $currentLocale = $product->getTranslation()->getLocale();
        if (null === $currentLocale) {
            return $attributes;
        }
        $productAttributeDTOClass = $this->configuration->getTargetClass('product_attribute');
        foreach ($product->getAttributesByLocale($currentLocale, $currentLocale) as $attributeValue) {
            $attribute = $attributeValue->getAttribute();
            $currentLocale = $product->getTranslation()->getLocale();
            if (null !== $currentLocale && null !== $attribute) {
                $attribute->setCurrentLocale($currentLocale);
            }
            if (null === $attributeValue->getName() || null === $attributeValue->getValue()) {
                continue;
            }
            $attribute = $attributeValue->getAttribute();
            if (!$attribute instanceof SearchableInterface || (!$attribute->isSearchable() && !$attribute->isFilterable())) {
                continue;
            }
            $attributes[$attributeValue->getCode()] = $this->nestedMapper->map($attributeValue, $productAttributeDTOClass);
        }

        return $attributes;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getOptions(ProductInterface $product): array
    {
        $options = [];
        $currentLocale = $product->getTranslation()->getLocale();
        foreach ($product->getVariants() as $variant) {
            foreach ($variant->getOptionValues() as $optionValue) {
                if (null === $optionValue->getOption()) {
                    continue;
                }
                if (!isset($options[$optionValue->getOptionCode()])) {
                    $options[$optionValue->getOptionCode()] = [
                        'name' => $optionValue->getOption()->getTranslation($currentLocale)->getName(),
                        'values' => [],
                    ];
                }
                $isEnabled = ($options[$optionValue->getOptionCode()]['values'][$optionValue->getCode()]['enabled'] ?? false)
                    || $variant->isEnabled();
                // A variant option is considered to be in stock if the current option is enabled and is in stock
                $isInStock = ($options[$optionValue->getOptionCode()]['values'][$optionValue->getCode()]['is_in_stock'] ?? false)
                    || ($variant->isEnabled() && $this->isProductVariantInStock($variant));
                $options[$optionValue->getOptionCode()]['values'][$optionValue->getCode()] = [
                    'value' => $optionValue->getTranslation($currentLocale)->getValue(),
                    'enabled' => $isEnabled,
                    'is_in_stock' => $isInStock,
                ];
            }
        }

        foreach ($options as $optionCode => $optionValues) {
            $options[$optionCode]['values'] = array_values($optionValues['values']);
        }

        return $options;
    }

    public function getVariants(ProductInterface $product): array
    {
        $variants = [];
        $productVariantDTOClass = $this->configuration->getTargetClass('product_variant');
        foreach ($product->getEnabledVariants() as $variant) {
            $variants[] = $this->nestedMapper->map($variant, $productVariantDTOClass);
        }

        return $variants;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getPrices(ProductInterface $product): array
    {
        $prices = [];
        foreach ($product->getChannels() as $channel) {
            /** @var ChannelInterface $channel */
            $this->channelSimulationContext->setChannel($channel);

            try {
                if (
                    null === ($variant = $this->productVariantResolver->getVariant($product))
                    || !$variant instanceof ModelProductVariantInterface
                    || null === ($channelPricing = $variant->getChannelPricingForChannel($channel))
                ) {
                    continue;
                }
            } finally {
                $this->channelSimulationContext->setChannel(null);
            }
            $prices[] = $this->nestedMapper->map(
                $channelPricing,
                $this->configuration->getTargetClass('pricing')
            );
        }

        return $prices;
    }

    private function isProductVariantInStock(ProductVariantInterface $productVariant): bool
    {
        if (!$productVariant instanceof StockableInterface) {
            return true;
        }

        return $this->availabilityChecker->isStockAvailable($productVariant);
    }
}
