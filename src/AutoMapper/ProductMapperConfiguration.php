<?php

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper;

use App\Entity\Product\Product;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use MonsieurBiz\SyliusSearchPlugin\Event\ProductMapperConfigurationEvent;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Product as ProductDTO;
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

        $metadata->forMember('price', function (Product $product) {
            return '1000';
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
