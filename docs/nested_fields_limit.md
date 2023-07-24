@TODO

```php
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
        if ('monsieurbiz_product' !== $event->getIndexCode()) {
            return;
        }

        $mapping = $event->getMapping();
        if (null === $mapping || !$mapping->offsetExists('mappings')) {
            return;
        }

        $settings = $mapping->offsetGet('settings') ?? [];
        $mapping->offsetSet('settings', array_merge($settings, ['mapping.nested_fields.limit' => 100])); // Increase the limit of nested fields
    }
}
```
