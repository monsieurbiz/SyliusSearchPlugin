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
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;

final class VariantMapperConfiguration implements MapperConfigurationInterface
{
    private Configuration $configuration;
    private AvailabilityCheckerInterface $availabilityChecker;

    public function __construct(Configuration $configuration, AvailabilityCheckerInterface $availabilityChecker)
    {
        $this->configuration = $configuration;
        $this->availabilityChecker = $availabilityChecker;
    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        $metadata->forMember('is_in_stock', function(ProductVariantInterface $productVariant): bool {
            return $this->availabilityChecker->isStockAvailable($productVariant);
        });

        $metadata->forMember('options', function(ProductVariantInterface $productVariant): array {
            $currentLocale = $productVariant->getTranslation()->getLocale();
            $options = [];
            foreach ($productVariant->getOptionValues() as $optionValue) {
                $options[$optionValue->getOptionCode()] = [
                    'name' => $optionValue->getName(),
                    'code' => $optionValue->getCode(),
                    'value' => $optionValue->getTranslation($currentLocale)->getValue(),
                ];
            }

            return $options;
        });
    }

    public function getSource(): string
    {
        return $this->configuration->getSourceClass('product_variant');
    }

    public function getTarget(): string
    {
        return $this->configuration->getTargetClass('product_variant');
    }
}
