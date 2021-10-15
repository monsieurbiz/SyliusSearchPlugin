<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon as DocumentTaxon;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Result;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultInterface;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Image;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\TaxonInterface;
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
     * @return ResultInterface
     */
    public function createResult(): ResultInterface
    {
        return new Result();
    }

    /**
     * @param string $locale
     *
     * @return ResultInterface
     */
    public function convertToDocument(string $locale): ResultInterface
    {
        $document = $this->createResult();

        // Document data
        $document->setType($this->getDocumentType());
        $document->setCode($this->getCode());
        $document->setId($this->getId());
        $document->setEnabled($this->isEnabled());
        $document->setSlug($this->getTranslation($locale)->getSlug());

        $document = $this->addImagesInDocument($document);
        $document = $this->addChannelsInDocument($document);
        $document = $this->addPricesInDocument($document);
        $document = $this->addTaxonsInDocument($document, $locale);

        $document->addAttribute('name', 'Name', [$this->getTranslation($locale)->getName()], $locale, 50);
        $document->addAttribute('description', 'Description', [$this->getTranslation($locale)->getDescription()], $locale, 10);
        $document->addAttribute('short_description', 'Short description', [$this->getTranslation($locale)->getShortDescription()], $locale, 10);
        $document->addAttribute('created_at', 'Creation Date', [$this->getCreatedAt()], $locale, 1);

        $document = $this->addAttributesInDocument($document, $locale);

        return $this->addOptionsInDocument($document, $locale);
    }

    /**
     * @param ResultInterface $document
     *
     * @return ResultInterface
     */
    protected function addImagesInDocument(ResultInterface $document): ResultInterface
    {
        /** @var Image $image */
        if ($image = $this->getImages()->first()) {
            $document->setImage($image->getPath());
        }

        return $document;
    }

    /**
     * @param ResultInterface $document
     *
     * @return ResultInterface
     */
    protected function addChannelsInDocument(ResultInterface $document): ResultInterface
    {
        /** @var Channel $channel */
        foreach ($this->getChannels() as $channel) {
            $document->addChannel($channel->getCode());
        }

        return $document;
    }

    /**
     * @param ResultInterface $document
     *
     * @return ResultInterface
     */
    protected function addPricesInDocument(ResultInterface $document): ResultInterface
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
     * @param ResultInterface $document
     * @param string $locale
     *
     * @return ResultInterface
     */
    protected function addTaxonsInDocument(ResultInterface $document, string $locale): ResultInterface
    {
        /** @var TaxonInterface $mainTaxon */
        if ($mainTaxon = $this->getMainTaxon()) {
            $taxon = new DocumentTaxon();
            $taxon
                ->setName($mainTaxon->getTranslation($locale)->getName())
                ->setCode($mainTaxon->getCode())
                ->setPosition($mainTaxon->getPosition())
                ->setLevel($mainTaxon->getLevel())
            ;
            $document->setMainTaxon($taxon);
        }

        /** @var ProductTaxonInterface $productTaxon */
        foreach ($this->getProductTaxons() as $productTaxon) {
            $document->addTaxon(
                $productTaxon->getTaxon()->getCode(),
                $productTaxon->getTaxon()->getTranslation($locale)->getName(),
                $productTaxon->getTaxon()->getPosition(),
                $productTaxon->getTaxon()->getLevel(),
                $productTaxon->getPosition()
            );
        }

        return $document;
    }

    /**
     * @param ResultInterface $document
     * @param string $locale
     *
     * @return ResultInterface
     */
    protected function addAttributesInDocument(ResultInterface $document, string $locale): ResultInterface
    {
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
            $document->addAttribute($attribute->getCode(), $attribute->getName(), $attributeValues, $attribute->getLocaleCode() ?? $locale, 1);
        }

        return $document;
    }

    /**
     * @param Result $document
     * @param string $locale
     *
     * @return Result
     */
    protected function addOptionsInDocument(ResultInterface $document, string $locale): ResultInterface
    {
        $options = [];
        foreach ($this->getVariants() as $variant) {
            /** @var \App\Entity\Product\ProductVariant $variant */
            foreach ($variant->getOptionValues() as $val) {
                if (!isset($options[$val->getOption()->getCode()])) {
                    $options[$val->getOption()->getCode()] = [
                        'name' => $val->getOption()->getTranslation($locale)->getName(),
                        'values' => [],
                    ];
                }
                $options[$val->getOption()->getCode()]['values'][$val->getCode()] = $val->getTranslation($locale)->getValue();
            }
        }

        foreach ($options as $optionCode => $option) {
            $document->addAttribute($optionCode, $option['name'], array_values($option['values']), $locale, 1);
        }

        return $document;
    }

    /**
     * @param $channel
     *
     * @return null
     */
    private function getCheapestVariantForChannel($channel)
    {
        $cheapestVariant = null;
        $cheapestPrice = null;
        $variants = $this->getEnabledVariants();
        foreach ($variants as $variant) {
            $channelPrice = $variant->getChannelPricingForChannel($channel);
            if (null === $cheapestPrice || $channelPrice->getPrice() < $cheapestPrice) {
                $cheapestPrice = $channelPrice->getPrice();
                $cheapestVariant = $variant;
            }
        }

        return $cheapestVariant;
    }
}
