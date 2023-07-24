# Add custom values to your indexed entity

## Add custom value for a product

In our example we will add the `short_description` product field to the indexed content.

First, declares your services : 

```yaml
    App\Search\EventListener\AppendProductMappingSubscriber:
        autoconfigure: true

    App\Search\Automapper\DecorateProductMapperConfiguration:
        autowire: true
        decorates: MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductMapperConfiguration
        arguments:
            - '@.inner'
```

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
        // We work on products, ignore the rest
        if ('monsieurbiz_product' !== $event->getIndexCode()) {
            return;
        }

        $mapping = $event->getMapping();
        if (null === $mapping || !$mapping->offsetExists('mappings')) {
            return;
        }

        $mappings['properties']['short_description'] = [
            'type' => 'text',
        ];

        $mapping->offsetSet('mappings', $mappings);
    }
}
```
