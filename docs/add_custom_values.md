# Add custom values to your indexed entity

## Add custom value for a product

In our example we will add the `short_description` product field to the indexed content.

- [Add your elasticsearch config path in `monsieurbiz_sylius_search.elastically_configuration_paths`](../dist/config/packages/monsieurbiz_sylius_search_plugin.yaml#L9)
- [Extend the product mapping to add the field](../dist/config/search/elasticsearch/monsieurbiz_product_mapping.yaml)
- [Register the product mapper decorator](../dist/config/search/services.yaml)
- [Create ProductMapperDecorator](../dist/src/Search/Mapper/ProductMapperDecorator.php)

`ProductMapperDecorator` implements the plugin's
[`DocumentMappingInterface`](../src/Mapper/DocumentMappingInterface.php) and
decorates `MonsieurBiz\SyliusSearchPlugin\Mapper\ProductMapper`. Its `$inner`
argument is the decorated `DocumentMappingInterface` service (`@.inner`). It
delegates `supports()` and `map()` to that service, then writes
`short_description` on the returned DTO through Symfony's PropertyAccessor.
Keep the example's autowiring and explicit decoration configuration.

**Search 3.0 breaking change:** additional matching-name properties are no longer
discovered automatically. Add them explicitly in a decorator or custom mapping,
and provide a writable target property (or the example's dynamic DTO support).
The `AutoMapper\ConfigurationInterface` / `Configuration` classes and
`automapper_classes` source/target keys remain for backwards compatibility; they
select classes, not an automatic field-discovery mechanism. Jane still generates
DTOs and normalizers. Search mapping does not read or alter API serializer metadata.

After clearing your application cache and repopulating the index, you will have
the `item.short_description` variable available in your templates.

## Search on the custom value

With only the decorator, you will not be able to search in the content of the new field.
You have to change parameters to define the fields to search for the search page and the instant search.

- [Add `short_description` in `monsieurbiz.search.product.search.fields_to_search`](../dist/config/search/config.yaml)
- [Add `short_description` in `monsieurbiz.search.product.instant.fields_to_search`](../dist/config/search/config.yaml)
