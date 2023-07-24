# UPGRADE FROM v1.X.X TO v2.0.x

1. We've changed the `.env` variable to add a messenger transport DSN to manage reindex queue.

```diff
-MONSIEURBIZ_SEARCHPLUGIN_ES_HOST=localhost
-MONSIEURBIZ_SEARCHPLUGIN_ES_PORT=9200
+MONSIEURBIZ_SEARCHPLUGIN_MESSENGER_TRANSPORT_DSN=doctrine://default
+MONSIEURBIZ_SEARCHPLUGIN_ES_HOST=${ELASTICSEARCH_HOST:-localhost}
+MONSIEURBIZ_SEARCHPLUGIN_ES_PORT=${ELASTICSEARCH_PORT:-9200}
``````

You can use a cron to consume it every minutes : 

`bin/console messenger:consume async_search --time-limit=60 --memory-limit=256M --env=prod`

Or use a [supervisor](https://symfony.com/doc/current/messenger.html#supervisor-configuration) or [systemd](https://symfony.com/doc/current/messenger.html#systemd-configuration) to manage it.

2. Add `AutomapperBundle` to your `bundles.php` file:

```diff
+   Jane\Bundle\AutoMapperBundle\JaneAutoMapperBundle::class => ['all' => true],
```

3. Change the `monsieurbiz_search_plugin.yaml` config file to remove these part:

```diff
imports:
    - { resource: "@MonsieurBizSyliusSearchPlugin/Resources/config/config.yaml" }
-
-monsieur_biz_sylius_search:
-    files:
-        search: '%kernel.project_dir%/vendor/monsieurbiz/sylius-search-plugin/src/Resources/config/elasticsearch/queries/search.yaml'
-        instant: '%kernel.project_dir%/vendor/monsieurbiz/sylius-search-plugin/src/Resources/config/elasticsearch/queries/instant.yaml'
-        taxon: '%kernel.project_dir%/vendor/monsieurbiz/sylius-search-plugin/src/Resources/config/elasticsearch/queries/taxon.yaml'
-    documentable_classes : []
-    grid:
-        limits:
-            taxon: [9, 18, 27]
-            search: [9, 18, 27]
-        default_limit:
-            taxon: 9
-            search: 9
-            instant: 10
-        sorting:
-            taxon: ['name', 'price', 'created_at']
-            search: ['name', 'price', 'created_at']
-        filters:
-            apply_manually: false # Will refresh the filters depending on applied filters after you apply it manually
-            use_main_taxon: true # Use main taxon for the taxon filter, else use the taxons
-
-# Remove header form
-services:
-    monsieurbiz_sylius_search.block_event_listener.layout.header:
-        class: Sylius\Bundle\UiBundle\Block\BlockEventListener
-        tags: []
```

4. Make your `ProductAttribute` entity implements the `SearchableInterface` and use the `SearchableTrait`

```diff
  * @ORM\Entity
  * @ORM\Table(name="sylius_product_attribute")
  */
-class ProductAttribute extends BaseProductAttribute implements FilterableInterface
+class ProductAttribute extends BaseProductAttribute implements SearchableInterface
 {
-    use FilterableTrait;
+    use SearchableTrait;
 
     protected function createTranslation(): AttributeTranslationInterface
     {
```

5. Make your `ProductOption` entity implements the `SearchableInterface` and use the `SearchableTrait`

```diff
  * @ORM\Entity
  * @ORM\Table(name="sylius_product_option")
  */
-class ProductOption extends BaseProductOption implements FilterableInterface
+class ProductOption extends BaseProductOption implements SearchableInterface
 {
-    use FilterableTrait;
+    use SearchableTrait;
 
     protected function createTranslation(): ProductOptionTranslationInterface
     {
```

6. The `Product` entity doesn't need to implement the `DocumentableInterface` anymore. You can remove it.

7. The way to add some additionnal data changed.

Before we should have this in your Product entity for example : 

```php
     use DocumentableProductTrait {
        convertToDocument as parentConvertToDocument;
    }

    public function convertToDocument(string $locale): Result
    {
        $document = $this->parentConvertToDocument($locale);

        if ($this->getCustomValue()) {
            $document->addAttribute('custom_value', 'Custom Value', [$this->getCustomValue()], $locale, 1);
        }

        return $document;
    }
```

Now please refer to the [Add custom value](docs/add_custom_values.md) documentation.

@TODO - Templating part
