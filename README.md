[![Banner of Sylius Search plugin](docs/images/banner.jpg)](https://monsieurbiz.com/agence-web-experte-sylius)

<h1 align="center">Search</h1>

[![Search Plugin license](https://img.shields.io/github/license/monsieurbiz/SyliusSearchPlugin?public)](https://github.com/monsieurbiz/SyliusSearchPlugin/blob/master/LICENSE.txt)
[![Tests Status](https://img.shields.io/github/actions/workflow/status/monsieurbiz/SyliusSearchPlugin/tests.yaml?branch=master&logo=github)](https://github.com/monsieurbiz/SyliusCmsPagePlugin/actions?query=workflow%3ATests)
[![Recipe Status](https://img.shields.io/github/actions/workflow/status/monsieurbiz/SyliusSearchPlugin/recipe.yaml?branch=master&label=recipes&logo=github)](https://github.com/monsieurbiz/SyliusCmsPagePlugin/actions?query=workflow%3ASecurity)
[![Security Status](https://img.shields.io/github/actions/workflow/status/monsieurbiz/SyliusSearchPlugin/security.yaml?branch=master&label=security&logo=github)](https://github.com/monsieurbiz/SyliusCmsPagePlugin/actions?query=workflow%3ASecurity)


A search plugin for Sylius using [Elastically](https://github.com/jolicode/elastically) and [Jane](https://github.com/janephp/janephp).

## Compatibility

| Sylius Version | PHP Version     |
|----------------|-----------------|
| 1.12           | 8.1 - 8.2 - 8.3 |
| 1.13           | 8.1 - 8.2 - 8.3 |
| 1.14           | 8.1 - 8.2 - 8.3 |

## Installation

If you want to use our recipes, you can configure your composer.json by running:

```bash
composer config --no-plugins --json extra.symfony.endpoint '["https://api.github.com/repos/monsieurbiz/symfony-recipes/contents/index.json?ref=flex/master","flex://defaults"]'
```

This is the last version using `jane-php/automapper`, which requires `nikic/php-parser="^4.0"`.
The next version will use `jolicode/automapper`, which is compatible with `nikic/php-parser="^5.0"`.

```bash
composer require --no-progress --no-interaction nikic/php-parser="^4.0"
composer require monsieurbiz/sylius-search-plugin
```

If you are using Symfony Flex, the recipe will automatically do some actions.

<details>
<summary>For the installation without flex, follow these additional steps</summary>
<p>

Change your `config/bundles.php` file to add this line for the plugin declaration:
```php
<?php

return [
    //..
    MonsieurBiz\SyliusSearchPlugin\MonsieurBizSyliusSearchPlugin::class => ['all' => true],
    Jane\Bundle\AutoMapperBundle\JaneAutoMapperBundle::class => ['all' => true],
];
```

Create the config file in `config/packages/monsieurbiz_sylius_search_plugin.yaml`:

```yaml
imports:
  - { resource: "@MonsieurBizSyliusSearchPlugin/Resources/config/config.yaml" }
```

Create the route config file in `config/routes/monsieurbiz_sylius_search_plugin.yaml`:

```yaml
monsieurbiz_search_plugin:
  resource: "@MonsieurBizSyliusSearchPlugin/Resources/config/routing.yaml"
```

Copy the override templates:

```shell
cp -Rv vendor/monsieurbiz/sylius-search-plugin/src/Resources/templates/* templates/
```

Finally configure plugin in your .env file by adding these lines at the end :

```
###> MonsieurBizSearchPlugin ###
MONSIEURBIZ_SEARCHPLUGIN_MESSENGER_TRANSPORT_DSN=doctrine://default
MONSIEURBIZ_SEARCHPLUGIN_ES_HOST=${ELASTICSEARCH_HOST:-localhost}
MONSIEURBIZ_SEARCHPLUGIN_ES_PORT=${ELASTICSEARCH_PORT:-9200}
MONSIEURBIZ_SEARCHPLUGIN_ES_URL=http://${MONSIEURBIZ_SEARCHPLUGIN_ES_HOST}:${MONSIEURBIZ_SEARCHPLUGIN_ES_PORT}/
###< MonsieurBizSearchPlugin ###
```

</p>
</details>

1. Install Elasticsearch 💪. See [Infrastructure](#infrastructure) below.

2. Your `ProductAttribute` and `ProductOption` entities need to implement the `MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface` interface and use the `MonsieurBiz\SyliusSearchPlugin\Model\Product\SearchableTrait` trait. Example with the `ProductAttribute`:

```diff
namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
+use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
+use MonsieurBiz\SyliusSearchPlugin\Model\Product\SearchableTrait;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_attribute")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_attribute')]
-class ProductAttribute extends BaseProductAttribute
+class ProductAttribute extends BaseProductAttribute implements SearchableInterface
{
+    use SearchableTrait;

    protected function createTranslation(): AttributeTranslationInterface
    {
        return new ProductAttributeTranslation();
```

3. You need to run a diff of your doctrine's migrations: `console doctrine:migrations:diff`. Don't forget to run it! (`console doctrine:migrations:migrate`)

4. Run the populate command.

## Documentation

[Documentation is available in the *docs* folder.](docs/index.md)

## Infrastructure

The plugin was developed for Elasticsearch 7.16.x versions. You need to have analysis-icu and analysis-phonetic elasticsearch plugin installed.

## Other information

### Jane

We are using [Jane](https://github.com/janephp/janephp) to create a DTO (Data-transfer object).  
Generated classes are on `generated` folder.  
Jane configuration and JSON Schema are on `src/Resources/config/jane` folder. 

To rebuild generated class during plugin development, we are using : 

```bash
symfony php vendor/bin/jane generate --config-file=src/Resources/config/jane/jane-configuration.php
```

### Elastically

The [Elastically](https://github.com/jolicode/elastically) Client is configured in `src/Resources/config/services.yaml` file.  
You can customize it in your `.env` file or if you want in `config/services.yaml`.  
Analyzers and YAML mappings are on `src/Resources/config/elasticsearch` folder.
