# Disable the ElasticsearchChecker

The plugin now checks if the Elasticsearch server is running before each search. 

If you want to disable this feature, you can do it by adding the following configuration:

```yaml
services:
    monsieurbiz.search.checker.elasticsearch_checker:
        class: MonsieurBiz\SyliusSearchPlugin\Checker\FakeElasticsearchChecker
```
