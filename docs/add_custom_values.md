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
You have to redeclare `monsieurbiz.search.request.query_filter.product_search.search_term_filter` 
and `monsieurbiz.search.request.query_filter.product_instant_search.search_term_filter` services 
to add the `short_description` fields.

- [Add `short_description` in search term filter](../dist/src/Resources/config/services.yaml#L34)
