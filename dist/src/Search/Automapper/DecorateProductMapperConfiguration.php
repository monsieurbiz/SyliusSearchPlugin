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

namespace App\Search\Automapper;

use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductMapperConfiguration;
use Sylius\Component\Core\Model\ProductInterface;

final class DecorateProductMapperConfiguration implements MapperConfigurationInterface
{
    private ProductMapperConfiguration $decoratedConfiguration;

    public function __construct(
        ProductMapperConfiguration $decoratedConfiguration
    ) {
        $this->decoratedConfiguration = $decoratedConfiguration;
    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        if (!$metadata instanceof MapperMetadata) {
            return;
        }
        $this->decoratedConfiguration->process($metadata);

        $metadata->forMember('short_description', function (ProductInterface $product): ?string {
            // Your logic here
            // In our case it's a simple getter
            return $product->getShortDescription();
        });
    }

    public function getSource(): string
    {
        return $this->decoratedConfiguration->getSource();
    }

    public function getTarget(): string
    {
        return $this->decoratedConfiguration->getTarget();
    }
}
