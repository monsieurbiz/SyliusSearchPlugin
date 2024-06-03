<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader;

use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;

class SelectReader implements ReaderInterface
{
    private string $defaultLocaleCode;

    public function __construct(TranslationLocaleProviderInterface $localeProvider)
    {
        $this->defaultLocaleCode = $localeProvider->getDefaultLocaleCode();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getValue(ProductAttributeValueInterface $productAttribute)
    {
        if (null === $productAttribute->getAttribute()) {
            return '';
        }

        $currentLocale = $productAttribute->getLocaleCode();
        $attribute = $productAttribute->getAttribute();
        if (null !== $attribute->getTranslation()->getLocale()) {
            $currentLocale = $attribute->getTranslation()->getLocale();
        }
        $choices = $productAttribute->getAttribute()->getConfiguration()['choices'] ?? [];
        $productAttributeValue = $productAttribute->getValue();
        if (!is_iterable($productAttributeValue)) {
            $productAttributeValue = [$productAttributeValue];
        }

        $result = [];
        foreach ($productAttributeValue as $value) {
            $locale = $currentLocale;
            if (!isset($choices[$value][$locale])) {
                $locale = $this->defaultLocaleCode;
            }
            $result[] = $choices[$value][$locale];
        }

        return $result;
    }

    public static function getReaderCode(): string
    {
        return 'select';
    }
}
