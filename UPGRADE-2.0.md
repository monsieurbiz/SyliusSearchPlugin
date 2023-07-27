# UPGRADE FROM `v2.0.x` TO `v2.1.x`

- Remove `MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\ProductSearchRegistry` service to use an iterator of services tagged `monsieurbiz.search.request.product_search_filter`
- Remove `monsieurbiz.search.request.query_filter.product_instant_search_registry` service to use an iterator of services tagged `monsieurbiz.search.request.product_instant_search_filter`
- Remove `MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\ProductTaxonRegistry` service to use an iterator of services tagged `monsieurbiz.search.request.product_taxon_filter`
- Remove `MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\ProductTaxonRegistry` service to use an iterator of services tagged `monsieurbiz.search.request.product_post_filter`
- Remove `MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\ProductSorterRegistry` service to use an iterator of services tagged `monsieurbiz.search.request.product_sorter`
- Remove `\MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\ProductFunctionScoreRegistry` service to use an iterator of services tagged `monsieurbiz.search.request.product_function_score`
- The `MonsieurBiz\SyliusSearchPlugin\Mapping\YamlWithLocaleProvider` is no longer a decorator. Some constructor parameters are removed : `$decorated`, `$configurationDirectory` and `$attributeRepository`, and we add `$fileLocator` and `$configurationDirectories`.
- New setting `monsieurbiz_sylius_search.elastically_configuration_paths` to define paths of elasticsearch mapping files. By default it's `['@MonsieurBizSyliusSearchPlugin/Resources/config/elasticsearch']`.
- New method `deleteByDocumentIds` in the `MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface` interface
- Deprecated the method `deleteByDocuments` in the `MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface` interface. Use `deleteByDocumentIds` instead. 
- `ChannelFilter` and `EnabledFilter` in `MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\Product` were moved to `MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter`
- A fallback on the Sylius' taxon display is now used to keep your pages even if you Elasticsearch instance is down. If you want to disable it, check the [FakeElasticsearchChecker](docs/disable_elasticsearch_checker.md)

# UPGRADE FROM v1.X.X TO v2.0.x

1. We've changed the `.env` variable to add a messenger transport DSN to manage reindex queue.

```diff
-MONSIEURBIZ_SEARCHPLUGIN_ES_HOST=localhost
-MONSIEURBIZ_SEARCHPLUGIN_ES_PORT=9200
+MONSIEURBIZ_SEARCHPLUGIN_MESSENGER_TRANSPORT_DSN=doctrine://default
+MONSIEURBIZ_SEARCHPLUGIN_ES_HOST=${ELASTICSEARCH_HOST:-localhost}
+MONSIEURBIZ_SEARCHPLUGIN_ES_PORT=${ELASTICSEARCH_PORT:-9200}
+MONSIEURBIZ_SEARCHPLUGIN_ES_URL=http://${MONSIEURBIZ_SEARCHPLUGIN_ES_HOST}:${MONSIEURBIZ_SEARCHPLUGIN_ES_PORT}/
```

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

6. You need to run a diff of your doctrine's migrations: `console doctrine:migrations:diff`. Don't forget to run it! (`console doctrine:migrations:migrate`)

7. The `Product` entity doesn't need to implement the `DocumentableInterface` anymore. You can remove it.

8. The way to add some additionnal data changed.

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

9. The templates inside the folder `@MonsieurBizSyliusSearchPlugin/Common` does not exist anymore.  

The template architecture is
- `@MonsieurBizSyliusSearchPlugin/Search` for the search page
- `@MonsieurBizSyliusSearchPlugin/Instant` for the instant search
- `@MonsieurBizSyliusSearchPlugin/Taxon` for the taxon page

10. Analyzers are manage by locale, example with `src/Resources/config/elasticsearch/analyzers_fr.yaml`

You can also have the full locale code if you need `src/Resources/config/elasticsearch/analyzers_fr_FR.yaml`

11. Mapping is now global so the files `src/Resources/config/elasticsearch/mappings/documents-<locale_code>_mapping.yaml` were removed.

12. Queries are managed differently so the files in `src/Resources/config/elasticsearch/queries` were removed.

If you changed this part, have a look of [the documention](./docs/index.md) to know how to customize your indexed content and your results.
