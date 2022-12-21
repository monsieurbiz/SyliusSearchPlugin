# Tips

## Add prefix for index names and aliases

In the `config/packages/monsieurbiz_sylius_search_plugin.yaml` file, add the `prefix` node for documents that need a prefix in the names of indexes and aliases.

Example, for the products index:

```diff
imports:
  - { resource: "@MonsieurBizSyliusSearchPlugin/Resources/config/config.yaml" }

+monsieurbiz_sylius_search:
+  documents:
+    monsieurbiz_product:
+     prefix: 'myproject' # define a custom index prefix
```
