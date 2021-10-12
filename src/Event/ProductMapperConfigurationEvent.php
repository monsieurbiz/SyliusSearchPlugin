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
