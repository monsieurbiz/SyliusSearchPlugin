# Add custom sorts

## Create your own sorter service

You can create your own sorter service by implementing the `SorterInterface` interface.  
For example, in your test application, [we have a short description sorter](../dist/src/Search/Request/Sorting/Product/ShortDescriptionSorter.php).

Add [the tag `monsieurbiz.search.request.product_sorter` to your service](../dist/src/Resources/config/services.yaml#L49).

## Replace existing sorter

In your `services.yaml` file, you can replace the existing sorter by your own service.

```yaml
services:
    MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product\PriceSorter:
        class: App\Search\Request\Sorting\Product\PriceSorter
        tags:
            - { name: monsieurbiz.search.request.product_sorter }
```

Don't forget to create your class!

If you want to keep the logic but add code before and/or after, you can also [decorate the service](https://symfony.com/doc/current/service_container/service_decoration.html).

## Display the sorter in the front

Override the [sorting.html.twig template](../dist/templates/bundles/MonsieurBizSyliusSearchPlugin/Search/_sorting.html.twig) to add your sort.

## Tips

A text field can't be used for sorting. In this case, you can [create a "keyword" subfield](../dist/src/Resources/config/elasticsearch/monsieurbiz_product_mapping.yaml#L5).
