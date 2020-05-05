<?php

declare(strict_types=1);

namespace Tests\MonsieurBiz\SyliusSearchPlugin\App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentResult;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Core\Model\ProductTranslation;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct implements DocumentableInterface
{
    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
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

        /** @var \Sylius\Component\Core\Model\Image $image */
        if ($image = $this->getImages()->first()) {
            $document->setImage($image->getPath());
        }

        /** @var \Sylius\Component\Core\Model\Channel $channel */
        foreach ($this->getChannels() as $channel) {
            $document->addChannel($channel->getCode());

            // TODO Get cheapest variant
            /** @var \Sylius\Component\Core\Model\ProductVariant $variant */
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
        /** @var \Sylius\Component\Attribute\Model\AttributeValueInterface $attribute */
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
}
