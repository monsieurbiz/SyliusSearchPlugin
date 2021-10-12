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
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Event\ProductMapperConfigurationEvent;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Product as ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductAttribute;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductMapperConfiguration implements MapperConfigurationInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        if (!$metadata instanceof MapperMetadata) {
            return;
        }

        $metadata->forMember('attributes', function(ProductInterface $product) {
            $attributes = [];
            /** @var \Sylius\Component\Product\Model\ProductAttributeValue $attributeValue */
            foreach ($product->getAttributes() as $attributeValue) {
                if (null === $attributeValue->getName() || null === $attributeValue->getValue()) {
                    continue;
                }
                $attribute = $attributeValue->getAttribute();
                if ($attribute instanceof SearchableInterface && !$attribute->isSearchable() && !$attribute->isFilterable()) {
                    continue;
                }
                $attributeDTO = new ProductAttribute();
                $attributeDTO->setName($attributeValue->getName());
                $attributeDTO->setValue($attributeValue->getValue());
                $attributes[$attributeValue->getCode()] = $attributeDTO;
            }

            return $attributes;
        });

        $this->eventDispatcher->dispatch(
            new ProductMapperConfigurationEvent($metadata),
            ProductMapperConfigurationEvent::EVENT_NAME
        );
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
