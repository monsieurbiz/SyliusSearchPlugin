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
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader\ReaderInterface;
use RuntimeException;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

final class ProductAttributeValueConfiguration implements MapperConfigurationInterface
{
    private Configuration $configuration;
    /**
     * @var ReaderInterface[]
     */
    private array $productAttributeValueReaders;

    public function __construct(Configuration $configuration, array $productAttributeValueReaders = [])
    {
        $this->configuration = $configuration;
        $this->productAttributeValueReaders = $productAttributeValueReaders;
    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        if (0 === \count($this->productAttributeValueReaders) || !\array_key_exists('default', $this->productAttributeValueReaders)) {
            throw new RuntimeException('Please define the "default" product attribute value reader');
        }

        $metadata->forMember('value', function(ProductAttributeValueInterface $productAttributeValue) {
            /** @var ReaderInterface $reader */
            $reader = $this->productAttributeValueReaders['default'];
            if (\array_key_exists($productAttributeValue->getType(), $this->productAttributeValueReaders)) {
                $reader = $this->productAttributeValueReaders[$productAttributeValue->getType()];
            }

            return $reader->getValue($productAttributeValue);
        });
    }

    public function getSource(): string
    {
        return $this->configuration->getSourceClass('product_attribute_value');
    }

    public function getTarget(): string
    {
        return $this->configuration->getTargetClass('product_attribute');
    }
}
