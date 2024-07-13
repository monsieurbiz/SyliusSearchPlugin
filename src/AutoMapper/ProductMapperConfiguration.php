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

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper;

use DateTimeInterface;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\AutoMapperInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
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

final class ProductMapperConfiguration implements MapperConfigurationInterface
{
    private ConfigurationInterface $configuration;

    private AutoMapperInterface $autoMapper;

    private ProductVariantResolverInterface $productVariantResolver;

    private AvailabilityCheckerInterface $availabilityChecker;

    private ChannelSimulationContext $channelSimulationContext;

    public function __construct(
        ConfigurationInterface $configuration,
        AutoMapperInterface $autoMapper,
        ProductVariantResolverInterface $productVariantResolver,
        AvailabilityCheckerInterface $availabilityChecker,
        ChannelSimulationContext $channelSimulationContext
    ) {
        $this->configuration = $configuration;
        $this->autoMapper = $autoMapper;
        $this->productVariantResolver = $productVariantResolver;
        $this->availabilityChecker = $availabilityChecker;
        $this->channelSimulationContext = $channelSimulationContext;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        if (!$metadata instanceof MapperMetadata) {
            return;
        }

        $metadata->forMember('id', function (ProductInterface $product): int {
            return $product->getId();
        });

        $metadata->forMember('code', function (ProductInterface $product): ?string {
            return $product->getCode();
        });

        $metadata->forMember('enabled', function (ProductInterface $product): bool {
            return $product->isEnabled();
        });

        $metadata->forMember('slug', function (ProductInterface $product): ?string {
            return $product->getSlug();
        });

        $metadata->forMember('name', function (ProductInterface $product): ?string {
            return $product->getName();
        });

        $metadata->forMember('description', function (ProductInterface $product): ?string {
            return $product->getDescription();
        });

        $metadata->forMember('created_at', function (ProductInterface $product): ?DateTimeInterface {
            return $product->getCreatedAt();
        });

        $metadata->forMember('images', function (ProductInterface $product): array {
            $images = [];
            $imageDTOClass = $this->configuration->getTargetClass('image');
            foreach ($product->getImages() as $image) {
                $images[] = $this->autoMapper->map($image, $imageDTOClass);
            }

            return $images;
        });

        $metadata->forMember('mainTaxon', function (ProductInterface $product) {
            $mainTaxon = $product->getMainTaxon();
            if (null === $mainTaxon) {
                return null;
            }

            $currentLocale = $product->getTranslation()->getLocale();
            if (null !== $currentLocale) {
                $mainTaxon->setCurrentLocale($currentLocale);
            }

            return $this->autoMapper->map($mainTaxon, $this->configuration->getTargetClass('taxon'));
        });

        $metadata->forMember('product_taxons', function (ProductInterface $product): array {
            return array_map(function (ProductTaxonInterface $productTaxon) use ($product) {
                $taxon = $productTaxon->getTaxon();
                $currentLocale = $product->getTranslation()->getLocale();
                if (null !== $currentLocale && null !== $taxon) {
                    $taxon->setCurrentLocale($currentLocale);
                }

                // todo add parent taxon in Taxon object with automapper
                return $this->autoMapper->map($productTaxon, $this->configuration->getTargetClass('product_taxon'));
            }, $product->getProductTaxons()->toArray());
        });

        $metadata->forMember('channels', function (ProductInterface $product): array {
            /** @phpstan-ignore-next-line */
            return array_map(function (ChannelInterface $channel) {
                return $this->autoMapper->map($channel, $this->configuration->getTargetClass('channel'));
            }, $product->getChannels()->toArray());
        });

        $metadata->forMember('attributes', [$this, 'getAttributes']);

        $metadata->forMember('options', [$this, 'getOptions']);

        $metadata->forMember('variants', [$this, 'getVariants']);

        $metadata->forMember('prices', [$this, 'getPrices']);
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
            $attributes[$attributeValue->getCode()] = $this->autoMapper->map($attributeValue, $productAttributeDTOClass);
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
            $variants[] = $this->autoMapper->map($variant, $productVariantDTOClass);
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
            if (
                null === ($variant = $this->productVariantResolver->getVariant($product))
                || !$variant instanceof ModelProductVariantInterface
                || null === ($channelPricing = $variant->getChannelPricingForChannel($channel))
            ) {
                $this->channelSimulationContext->setChannel(null);

                continue;
            }
            $this->channelSimulationContext->setChannel(null);
            $prices[] = $this->autoMapper->map(
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
