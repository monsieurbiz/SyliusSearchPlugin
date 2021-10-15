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

namespace MonsieurBiz\SyliusSearchPlugin\EventSubscriber;

use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AppendProductAttributeMappingSubscriber implements EventSubscriberInterface
{
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    public function __construct(ProductAttributeRepositoryInterface $productAttributeRepository)
    {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            MappingProviderEvent::EVENT_NAME => 'omMappingProvider',
        ];
    }

    public function omMappingProvider(MappingProviderEvent $event): void
    {
        if ('monsieurbiz_product' !== $event->getIndexCode()) {
            return;
        }
        $mapping = $event->getMapping();
        if (null === $mapping || !$mapping->offsetExists('mappings')) {
            return;
        }
        $mappings = $mapping->offsetGet('mappings');
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            if (!\array_key_exists('attributes', $mappings['properties'])) {
                $mappings['properties']['attributes'] = [
                    'type' => 'nested',
                    'properties' => [],
                ];
            }
            $mappings['properties']['attributes']['properties'][$productAttribute->getCode()] = [
                'type' => 'nested',
                'properties' => [
                    'code' => ['type' => 'keyword'],
                    'name' => ['type' => 'keyword'],
                    'value' => ['type' => 'text'],
                ],
            ];

            if ($productAttribute->isFilterable()) {
                $mappings['properties']['attributes']['properties'][$productAttribute->getCode()]['properties']['value']['fields'] = [
                    'keyword' => ['type' => 'keyword'],
                ];
            }

            if ($productAttribute->isSearchable()) {
                // TODO replace to a configurable value
                $mappings['properties']['attributes']['properties'][$productAttribute->getCode()]['properties']['value']['analyzer'] = 'search_standard';
            }
        }

        $mapping->offsetSet('mappings', $mappings);
    }
}
