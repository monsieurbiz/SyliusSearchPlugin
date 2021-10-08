<?php

namespace MonsieurBiz\SyliusSearchPlugin\Event;

use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ProductMapperConfigurationEvent extends Event
{
    public const EVENT_NAME = 'monsieurbiz.search.product.mapper.configuration';
    private MapperGeneratorMetadataInterface $mapperGeneratorMetadata;

    public function __construct(MapperGeneratorMetadataInterface $mapperGeneratorMetadata)
    {
        $this->mapperGeneratorMetadata = $mapperGeneratorMetadata;
    }

    public function getMapperGeneratorMetadata(): MapperGeneratorMetadataInterface
    {
        return $this->mapperGeneratorMetadata;
    }
}
