<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use MonsieurBiz\SyliusSearchPlugin\Model\DocumentResult;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Image;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Currency\Model\CurrencyInterface;

trait DocumentableProductTrait
{
    /**
     * @return string
     */
    public function getDocumentType(): string
    {
        return 'product';
    }

    /**
     * @param string $locale
     * @return DocumentResult
     */
    public function convertToDocument(string $locale): DocumentResult
    {
        $document = new DocumentResult();

        // Document data
        $document->setType($this->getDocumentType());
        $document->setCode($this->getCode());
        $document->setId($this->getId());
        $document->setEnabled($this->isEnabled());
        $document->setSlug($this->getTranslation($locale)->getSlug());

        $document = $this->addImagesInDocument($document);
        $document = $this->addChannelsInDocument($document);
        $document = $this->addPricesInDocument($document);
        $document = $this->addTaxonsInDocument($document);


        $document->addAttribute('name', 'Name', [$this->getTranslation($locale)->getName()], $locale, 50);
        $document->addAttribute('description', 'Description', [$this->getTranslation($locale)->getDescription()], $locale, 10);
        $document->addAttribute('short_description', 'Short description', [$this->getTranslation($locale)->getShortDescription()], $locale, 10);
        $document->addAttribute('created_at', 'Creation Date', [$this->getCreatedAt()], $locale, 1);

        $document = $this->addAttributesInDocument($document, $locale);

        return $document;
    }

    /**
     * @param DocumentResult $document
     * @return DocumentResult
     */
    protected function addImagesInDocument(DocumentResult $document): DocumentResult
    {
        /** @var Image $image */
        if ($image = $this->getImages()->first()) {
            $document->setImage($image->getPath());
        }

        return $document;
    }

    /**
     * @param DocumentResult $document
     * @return DocumentResult
     */
    protected function addChannelsInDocument(DocumentResult $document): DocumentResult
    {
        /** @var Channel $channel */
        foreach ($this->getChannels() as $channel) {
            $document->addChannel($channel->getCode());
        }

        return $document;
    }

    /**
     * @param DocumentResult $document
     * @return DocumentResult
     */
    protected function addPricesInDocument(DocumentResult $document): DocumentResult
    {
        /** @var Channel $channel */
        foreach ($this->getChannels() as $channel) {
            /** @var ProductVariant $variant */
            if ($variant = $this->getCheapestVariantForChannel($channel)) {
                $price = $variant->getChannelPricingForChannel($channel);

                /** @var CurrencyInterface $currency */
                foreach ($channel->getCurrencies() as $currency) {
                    $document->addPrice($channel->getCode(), $currency->getCode(), $price->getPrice());
                    if ($originalPrice = $price->getOriginalPrice()) {
                        $document->addOriginalPrice($channel->getCode(), $currency->getCode(), $originalPrice);
                    }
                }
            }
        }

        return $document;
    }

    /**
     * @param DocumentResult $document
     * @return DocumentResult
     */
    protected function addTaxonsInDocument(DocumentResult $document): DocumentResult
    {
        /** @var Taxon $taxon */
        if ($mainTaxon = $this->getMainTaxon()) {
            $document->setMainTaxon($mainTaxon->getCode());
        }

        /** @var ProductTaxon $productTaxon */
        foreach ($this->getProductTaxons() as $productTaxon) {
            $document->addTaxon($productTaxon->getTaxon()->getCode(), $productTaxon->getPosition());
        }

        return $document;
    }

    /**
     * @param DocumentResult $document
     * @param string $locale
     * @return DocumentResult
     */
    protected function addAttributesInDocument(DocumentResult $document, string $locale): DocumentResult
    {
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

    private function getCheapestVariantForChannel($channel)
    {
        $cheapestVariant = null;
        $cheapestPrice = null;
        $variants = $this->getVariants();
        foreach ($variants as $variant) {
            $channelPrice = $variant->getChannelPricingForChannel($channel);
            if ($cheapestPrice === null || $channelPrice->getPrice() < $cheapestPrice) {
                $cheapestPrice = $channelPrice->getPrice();
                $cheapestVariant = $variant;
            }
        }
        return $cheapestVariant;
    }
}
