# Add custom boosts (or function score) in requests

## Existing boosts

The plugin provides one boost by default `MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\Product\InStockWeightFunction`  that is disabled.

You have 2 parameters to modify the behavior of this boost:

```yaml
monsieurbiz.search.product.is_in_stock_scoring_boost: 0 # The value is used to multiply the document score (0 to disable the scoring boost)
monsieururbiz.search.product.apply_is_in_stock_scoring_boost_on: # The type of request where the boost is applied
    - !php/const MonsieurBiz\SyliusSearchPlugin\SearchRequestRequestInterface::SEARCH_TYPE
    - !php/const MonsieurBiz\SyliusSearchPlugin\SearchRequestInterface::TAXON_TYPE
    - !php/const MonsieurBiz\SyliusSearchPlugin\SearchRequestRequestInterface::INSTANT_TYPE
```

The boost is enabled when the value of `monsieurbiz.search.product.is_in_stock_scoring_boost` is greater than 0, the request type is in `monsieururbiz.search.product.apply_is_in_stock_scoring_boost_on` and the stock filter is not enabled (`monsieurbiz.search.product.enable_stock_filter`).

## Add a new boost

To create a new boost, you must

- [Create a new class that implements `MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreInterface`](../dist/src/Search/Request/FunctionScore/Product/BoostExpensiveProductFunction.php)
- [Tag it with `monsieurbiz.search.request.product_function_score`](../dist/src/Resources/config/services.yaml#L54)

In our example we will boost, in the search, the product with a price greater than 50.
