# Add custom values to your indexed entity

## Add custom value for a product

In our example we will add the `short_description` product field to the indexed content.

- [Add your elasticsearch config path in `monsieurbiz_sylius_search.elastically_configuration_paths`](../dist/config/packages/monsieurbiz_sylius_search_plugin.yaml#L9)
- [Extends the product mapping to add the field](../dist/src/Resources/config/elasticsearch/monsieurbiz_product_mapping.yaml)
- [Add a decorator for ProductMapperConfiguration](../dist/src/Resources/config/services.yaml#L22)
- [Create DecorateProductMapperConfiguration class](../dist/src/Search/Automapper/DecorateProductMapperConfiguration.php)

You will have the `item.short_description` variable available in your templates.

## Search on the custom value

With only the decorator, you will not be able to search in the content of the new field.
You have to change parameters to define the fields to search for the search page and the instant search.

- [Add `short_description` in `monsieurbiz.search.product.search.fields_to_search`](../dist/src/Resources/config/config.yaml#6)
- [Add `short_description` in `monsieurbiz.search.product.instant.fields_to_search`](../dist/src/Resources/config/config.yaml#13)
