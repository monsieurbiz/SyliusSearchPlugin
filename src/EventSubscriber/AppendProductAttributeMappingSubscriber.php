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

use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AppendProductAttributeMappingSubscriber implements EventSubscriberInterface
{
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductOptionRepositoryInterface $productOptionRepository;
    private string $fieldAnalyzer;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductOptionRepositoryInterface $productOptionRepository,
        string $fieldAnalyzer
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->fieldAnalyzer = $fieldAnalyzer;
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
        $attributesMapping = [];
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            $attributesMapping[$productAttribute->getCode()] = $this->getProductAttributeOrOptionProperties($productAttribute);
        }
        if (0 < \count($attributesMapping)) {
            $mappings['properties']['attributes'] = [
                'type' => 'nested',
                'properties' => $attributesMapping,
            ];
        }

        $optionsMapping = [];
        foreach ($this->productOptionRepository->findIsSearchableOrFilterable() as $productOption) {
            $optionsMapping[$productOption->getCode()] = $this->getProductAttributeOrOptionProperties($productOption);
        }
        if (0 < \count($optionsMapping)) {
            $mappings['properties']['variants']['properties']['options'] = [
                'type' => 'nested',
                'properties' => $optionsMapping,
            ];
        }

        $mapping->offsetSet('mappings', $mappings);
    }

    private function getProductAttributeOrOptionProperties(SearchableInterface $productAttributeOrOption): array
    {
        $properties = [
            'type' => 'nested',
            'properties' => [
                'code' => ['type' => 'keyword'],
                'name' => ['type' => 'keyword'],
                'value' => ['type' => 'text'],
            ],
        ];

        if ($productAttributeOrOption->isFilterable()) {
            $properties['properties']['value']['fields'] = [
                'keyword' => ['type' => 'keyword'],
            ];
        }

        if ($productAttributeOrOption->isSearchable()) {
            $properties['properties']['value']['analyzer'] = $this->fieldAnalyzer;
        }

        return $properties;
    }
}
