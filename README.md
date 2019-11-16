# Documentation for MonsieurBizSearchPlugin

A search plugin for Sylius using [Jane](https://github.com/janephp/janephp) and [Elastically](https://github.com/jolicode/elastically).

## Infrastructure

### Development 

Elasticsearch is available on `9200` port : http://127.0.0.1:9200/
Cerebro on port `9000` : http://127.0.0.1:9000/#/overview?host=http:%2F%2Felasticsearch:9200 
Kibana on port `5601` : http://127.0.0.1:5601/ 

On your machine, Elasticsearch is available at http://127.0.0.1:9200/
In docker, Elasticsearch is available at http://elasticsearch:9200/
This is the second URL you have to put on Cerebro, Kibana and Elasticsearch if you want to connect to the cluster.

### Fortress

Elasticsearch is available on : http://elasticsearch.<FORTRESS_HOST>/
Cerebro on : http://cerebro.<FORTRESS_HOST>/
Kibana on : http://kibana.<FORTRESS_HOST>/

On your machine, Elasticsearch is available at http://127.0.0.1:9200/
In docker, Elasticsearch is available at http://elasticsearch:9200/
This is the second URL you have to put on Cerebro, Kibana and Elasticsearch if you want to connect to the cluster.

## Setup

Make your `Product` entity implements [DocumentableInterface](#Documentable objects).
Run the populate [command](#Command).

## Configuration

The default module configuration is : 

```yaml
monsieur_biz_search:
  search_file: '%kernel.project_dir%/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch/search.json'
  instant_file: '%kernel.project_dir%/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch/instant.json'
  documentable_classes :
    - 'App\Entity\Product'
```

You can customize it in `apps/sylius/config/packages/monsieur_biz_search.yaml`.

`search_file` is the JSON used to perform the search.
`instant_file` is the JSON used to perform the search.

The `{{QUERY}}` string inside is replaced in PHP by the query typed by the user.

`documentable_classes` is an array of entities which can be indexed in Elasticsearch.

## Documentable objects

If you want to index an object in the search index, your entity have to implements `App\MonsieurBizSearchPlugin\Model\DocumentableInterface` interface : 

```php
interface DocumentableInterface
{
    public function getDocumentType(): string;
    public function convertToDocument(string $locale): DocumentResult;
}
```

Here is an exemple for the product conversion : 

```php
    public function getDocumentType(): string
    {
        return 'product';
    }
    
    public function convertToDocument(string $locale): DocumentResult
    {
        $document = new DocumentResult();
        
        // Document data
        $document->setType($this->getDocumentType());
        $document->setCode($this->getCode());
        $document->setId($this->getId());
        $document->setEnabled($this->isEnabled());
        $document->setSlug($this->getTranslation($locale)->getSlug());
    
        /** @var Image $image */
        if ($image = $this->getImages()->first()) {
            $document->setImage($image->getPath());
        }
        
        /** @var Channel $channel */
        foreach ($this->getChannels() as $channel) {
            $document->addChannel($channel->getCode());
    
            // TODO Get cheapest variant
            /** @var ProductVariant $variant */
            if ($variant = $this->getVariants()->first()) {
                $price = $variant->getChannelPricingForChannel($channel);
                // TODO Index all currencies
                $document->addPrice($channel->getCode(), $channel->getBaseCurrency()->getCode(), $price->getPrice());
                if ($originalPrice = $price->getOriginalPrice()) {
                    $document->addOriginalPrice($channel->getCode(), $channel->getBaseCurrency()->getCode(), $originalPrice);
                }
            }
        }
        
        $document->addAttribute('name', 'Name', [$this->getTranslation($locale)->getName()], $locale, 50);
        $document->addAttribute('description', 'Description', [$this->getTranslation($locale)->getDescription()], $locale, 10);
        $document->addAttribute('short_description', 'Short description', [$this->getTranslation($locale)->getShortDescription()], $locale, 10);
    
        // TODO : Add fallback locale
        /** @var AttributeValueInterface $attribute */
        foreach ($this->getAttributesByLocale($locale, $locale) as $attribute) {
            $attributeValues = [];
            if (isset($attribute->getAttribute()->getConfiguration()['choices'])) {
                foreach ($attribute->getValue() as $value) {
                    $attributeValues[] = $attribute->getAttribute()->getConfiguration()['choices'][$value][$locale];
                }
            } else {
                $attributeValues[] = $attribute->getValue();
            }
            $document->addAttribute($attribute->getCode(), $attribute->getName(), $attributeValues, $attribute->getLocaleCode(), 1);
        }
        return $document;
    }
```

You can add everything you want !

## Score by attribute

Each document attribute can have a `score`. It means it can be more important than another.
For example, the product name in the exemple above has a score of `50`, and the description a score of `10` : 
```php
$document->addAttribute('name', 'Name', [$this->getTranslation($locale)->getName()], $locale, 50);
$document->addAttribute('description', 'Description', [$this->getTranslation($locale)->getDescription()], $locale, 10);
```

## Improve search accuracy

You can customize the search with your custom JSON files and modifying : 

```yaml
monsieur_biz_search:
  search_file: '%kernel.project_dir%/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch/search.json'
  instant_file: '%kernel.project_dir%/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch/instant.json'
```

## Indexed Documents

Indexed documents are all entities defines in `monsieur_biz_search.documentable_classes` dans implements `DocumentableInterface`.

```yaml
monsieur_biz_search:
   :
    - 'App\Entity\Product'
```

## Command

A symfony command is available to populate index : `console monsieurbiz:search:populate`

## Index on save

For product entity, we have a listener to add / update / delete document on save.
It is the `App\MonsieurBizSearchPlugin\EventListener\DocumentListener` class which : 
- `saveDocument` on `post_create` dans `post_update`
- `removeDocument` on `pre_delete`

If your entity implements `DocumentableInterface`, you can add listeners to manage entities modifications (Replace `<YOUR_ENTITY>` with your) :
```yaml
    app.event_listener.document_listener:
        class: App\MonsieurBizSearchPlugin\EventListener\DocumentListener
        arguments:
            - '@App\MonsieurBizSearchPlugin\Indexer\DocumentIndexer'
        tags:
            - { name: kernel.event_listener, event: sylius.<YOUR_ENTITY>.post_create, method: saveDocument }
            - { name: kernel.event_listener, event: sylius.<YOUR_ENTITY>.post_update, method: saveDocument }
            - { name: kernel.event_listener, event: sylius.<YOUR_ENTITY>.pre_delete, method: deleteDocument }
```

## Url Params

If you add a new entity in search index. You have to be able to generate an URL when you display it.
In order to do that, you can customize the `RenderDocumentUrl` twig extension : 
```php
public function getUrlParams(DocumentResult $document): UrlParamsProvider {
    switch ($document->getType()) {
        case "product" :
            return new UrlParamsProvider('sylius_shop_product_show', ['slug' => $document->getSlug(), '_locale' => $document->getLocale()]);
            break;
            
        // Add new case !
    }
    
    throw new NotSupportedTypeException(sprintf('Object type "%s" not supported to get URL', $this->getType()));
}
```

## Front customization

You can override all templates in your theme to : 
- Customize search results display page (`apps/sylius/src/MonsieurBizSearchPlugin/Resources/views/Search/`)
- Customize instant search display block (`apps/sylius/src/MonsieurBizSearchPlugin/Resources/views/Instant/`)
- Customize JS parameters (`apps/sylius/src/MonsieurBizSearchPlugin/Resources/views/Instant/instant_javascript.html.twig`)

## Jane

We are using [Jane](https://github.com/janephp/janephp) to create a DTO (Data-transfer object).
Generated classes are on `apps/sylius/src/MonsieurBizSearchPlugin/generated` folder.
Jane configuration and JSON Schema are on `apps/sylius/src/MonsieurBizSearchPlugin/Resources/config/jane` folder.

## Elastically

The [Elastically](https://github.com/jolicode/elastically) Client is configured in `apps/sylius/src/MonsieurBizSearchPlugin/Resources/config/services.yaml` file.
You can customize it if you want in `config/services.yaml`.
Analyzers and YAML mappings are on `apps/sylius/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch` folder.

You can also find JSON used bu plugin to perform the search on Elasticsearch : 
- `apps/sylius/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch/instant.json`
- `apps/sylius/src/MonsieurBizSearchPlugin/Resources/config/elasticsearch/search.json`

These JSON can be customized in another folder if you change the plugin config.
