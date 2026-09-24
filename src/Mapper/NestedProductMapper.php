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
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueResolver;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/** Explicit nested document fields, independent of API serializer metadata. */
final class NestedProductMapper implements DocumentMappingInterface
{
    private const SOURCES = [
        'image' => ImageInterface::class,
        'taxon' => TaxonInterface::class,
        'product_taxon' => ProductTaxonInterface::class,
        'channel' => ChannelInterface::class,
        'product_attribute' => ProductAttributeValueInterface::class,
        'product_variant' => ProductVariantInterface::class,
        'pricing' => ChannelPricingInterface::class,
    ];

    public function __construct(
        private ConfigurationInterface $configuration,
        private PropertyAccessorInterface $propertyAccessor,
        private AvailabilityCheckerInterface $availabilityChecker,
        private ProductAttributeValueResolver $attributeValueResolver
    ) {
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) Match the bounded set of configured source/target pairs. */
    public function supports(object $source, string $targetClass): bool
    {
        foreach (self::SOURCES as $key => $interface) {
            $sourceKey = 'product_attribute' === $key ? 'product_attribute_value' : $key;
            if ($source instanceof $interface && $targetClass === $this->configuration->getTargetClass($key) && is_a($source, $this->configuration->getSourceClass($sourceKey))) {
                return true;
            }
        }

        return false;
    }

    public function map(object $source, string $targetClass): object
    {
        if (!$this->supports($source, $targetClass)) {
            throw new InvalidArgumentException('Unsupported nested search document mapping.');
        }
        $values = match (true) {
            $source instanceof ImageInterface => ['path' => $source->getPath(), 'type' => $source->getType()],
            $source instanceof TaxonInterface => ['name' => $source->getName(), 'code' => $source->getCode(), 'position' => $source->getPosition(), 'level' => $source->getLevel()],
            $source instanceof ProductTaxonInterface => ['taxon' => $this->mapTaxon($source->getTaxon()), 'position' => $source->getPosition()],
            $source instanceof ChannelInterface => ['code' => $source->getCode()],
            $source instanceof ProductAttributeValueInterface => ['code' => $source->getCode(), 'name' => $source->getName(), 'value' => $this->attributeValueResolver->getProductAttributeValue($source)],
            $source instanceof ProductVariantInterface => ['code' => $source->getCode(), 'enabled' => $source->isEnabled(), 'is_in_stock' => $this->isInStock($source)],
            $source instanceof ChannelPricingInterface => ['channelCode' => $source->getChannelCode(), 'price' => $source->getPrice(), 'originalPrice' => $source->getOriginalPrice(), 'priceReduced' => $source->isPriceReduced()],
            default => throw new InvalidArgumentException('Unsupported nested source type.'),
        };
        $target = new $targetClass();
        foreach ($values as $property => $value) {
            if (null !== $value) {
                // Public setters preserve Jane's initialization flags and consumer overrides.
                $this->propertyAccessor->setValue($target, $property, $value);
            }
        }

        return $target;
    }

    private function mapTaxon(?TaxonInterface $taxon): ?object
    {
        return null === $taxon ? null : $this->map($taxon, $this->configuration->getTargetClass('taxon'));
    }

    private function isInStock(ProductVariantInterface $variant): bool
    {
        return !$variant instanceof StockableInterface || $this->availabilityChecker->isStockAvailable($variant);
    }
}
