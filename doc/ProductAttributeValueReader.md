# Product attribute value reader

## What is it?

A product attribute value reader is used to transform the value of an attribute into an indexable value for the elasticsearch document.

We have defined a reader for the native Sylius types:

- checkbox
- date
- datetime
- integer
- percent
- select
- textarea
- text

## Add or replace a reader

You have added a new attribute type, and you want to index its value.  
Or you want to change an existing reader.

In your `service.yaml`, you can add or replace a product attribute value reader :

**Create a Product Attribute Value Reader class** that implements the `\MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader\ReaderInterface` interface, and define the two methods:

- `getReaderCode`: this code matches the reader with the attribute type
- `getValue`: return the indexable value

```php
use \MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader\ReaderInterface;

class MyCustomReader implements ReaderInterface
{
    // ...
}
```

And add the `monsieurbiz.search.automapper.product_attribute_value_reader` tag on your custom reader :

```yaml
App\...\MyCustomReader:
    tags: [ 'monsieurbiz.search.automapper.product_attribute_value_reader' ]

```

To **replace** an existing product attribute value reader, your `getReaderCode` returns the attribute type code of the existing reader.
