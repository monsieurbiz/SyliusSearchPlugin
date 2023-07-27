# Add custom filters in products requests

## Create your own filter

1. You can create your own filter service by implementing the `\MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface` interface.

```php
<?php

namespace App\Search\Request\QueryFilter\Product;

class MyCustomQuery implements QueryFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        // Add your filter in the $boolQuery
    }
}
```

Take a look at the existing filters for the products query:

- [Channel filter](../src/Search/Request/QueryFilter/Product/ChannelFilter.php)
- [In stock filter](../src/Search/Request/QueryFilter/Product/ChannelFilter.php)

2. Add the tags, depending on the queries you wish to impact:

```yaml
App\Search\Request\QueryFilter\Product\MyCustomQuery:
    tags:
        - { name: monsieurbiz.search.request.product_search_filter }
        - { name: monsieurbiz.search.request.product_instant_search_filter }
        - { name: monsieurbiz.search.request.product_taxon_filter }
```

