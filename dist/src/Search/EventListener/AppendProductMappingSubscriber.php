<?php

declare(strict_types=1);

namespace App\Search\EventListener;

use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AppendProductMappingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MappingProviderEvent::EVENT_NAME => 'onMappingProvider',
        ];
    }

    public function onMappingProvider(MappingProviderEvent $event): void
    {
        // We only change product mapping
        if ('monsieurbiz_product' !== $event->getIndexCode()) {
            return;
        }

        $mapping = $event->getMapping();
        $mappings = $mapping->offsetGet('mappings') ?? [];

        $mappings['properties']['short_description'] = [
            'type' => 'text',
        ];

        $mapping->offsetSet('mappings', $mappings);
    }
}
