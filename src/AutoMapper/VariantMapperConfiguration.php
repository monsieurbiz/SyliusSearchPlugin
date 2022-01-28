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

use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;

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
        if (!$metadata instanceof MapperMetadata) {
            return;
        }

        $metadata->forMember('is_in_stock', function (ProductVariantInterface $productVariant): bool {
            if (!$productVariant instanceof StockableInterface) {
                return true;
            }

            return $this->availabilityChecker->isStockAvailable($productVariant);
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
