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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingParamException;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingPriceException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\generated\Model\Attributes;
use MonsieurBiz\SyliusSearchPlugin\generated\Model\Document;
use MonsieurBiz\SyliusSearchPlugin\generated\Model\Price;
use MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon;
use MonsieurBiz\SyliusSearchPlugin\Provider\UrlParamsProvider;

class Result extends Document implements ResultInterface
{
    /**
     * Document ID in elasticsearch.
     *
     * @throws MissingParamException
     */
    public function getUniqId(): string
    {
        if (!$this->getType()) {
            throw new MissingParamException('Missing "type" for document');
        }
        if (!$this->getId()) {
            throw new MissingParamException('Missing "ID" for document');
        }

        return sprintf('%s-%d', $this->getType(), $this->getId());
    }

    /**
     * @return Attributes
     */
    public function getAttribute(string $code): ?Attributes
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getCode() === $code) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @throws MissingPriceException
     */
    public function getPriceByChannelAndCurrency(string $channelCode, string $currencyCode): ?Price
    {
        if (null === $this->getPrice()) {
            return null;
        }
        foreach ($this->getPrice() as $price) {
            if ($price->getChannel() === $channelCode && $price->getCurrency() === $currencyCode) {
                return $price;
            }
        }

        throw new MissingPriceException(sprintf('Price not found for channel "%s" and currency "%s"', $channelCode, $currencyCode));
    }

    public function getOriginalPriceByChannelAndCurrency(string $channelCode, string $currencyCode): ?Price
    {
        if (null === $this->getOriginalPrice()) {
            return null;
        }

        foreach ($this->getOriginalPrice() as $price) {
            if ($price->getChannel() === $channelCode && $price->getCurrency() === $currencyCode) {
                return $price;
            }
        }

        return null;
    }

    /**
     * @throws MissingLocaleException
     */
    public function getLocale(): string
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getLocale()) {
                return $attribute->getLocale();
            }
        }

        throw new MissingLocaleException('Locale not found in document');
    }

    /**
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     */
    public function getUrlParams(): UrlParamsProvider
    {
        switch ($this->getType()) {
            case 'product':
                return new UrlParamsProvider('sylius_shop_product_show', ['slug' => $this->getSlug(), '_locale' => $this->getLocale()]);

                break;
        }

        throw new NotSupportedTypeException(sprintf('Object type "%s" not supported to get URL', $this->getType()));
    }

    /**
     * @return Result
     */
    public function addChannel(string $channel): self
    {
        $this->setChannel($this->getChannel() ? array_unique(array_merge($this->getChannel(), [$channel])) : [$channel]);

        return $this;
    }

    /**
     * @return Result
     */
    public function addTaxon(string $code, string $name, int $position, int $level, int $productPosition): ResultInterface
    {
        $taxon = new Taxon();
        $taxon->setCode($code)->setPosition($position)->setName($name)->setLevel($level)->setProductPosition($productPosition);
        $this->setTaxon($this->getTaxon() ? array_merge($this->getTaxon(), [$taxon]) : [$taxon]);

        return $this;
    }

    /**
     * @return Result
     */
    public function addPrice(string $channel, string $currency, int $value): ResultInterface
    {
        $price = new Price();
        $price->setChannel($channel)->setCurrency($currency)->setValue($value);
        $this->setPrice($this->getPrice() ? array_merge($this->getPrice(), [$price]) : [$price]);

        return $this;
    }

    /**
     * @return Result
     */
    public function addOriginalPrice(string $channel, string $currency, int $value): ResultInterface
    {
        $price = new Price();
        $price->setChannel($channel)->setCurrency($currency)->setValue($value);
        $this->setOriginalPrice($this->getOriginalPrice() ? array_merge($this->getOriginalPrice(), [$price]) : [$price]);

        return $this;
    }

    /**
     * @return Result
     */
    public function addAttribute(string $code, string $name, array $value, string $locale, int $score): ResultInterface
    {
        $attribute = new Attributes();
        $attribute->setCode($code)->setName($name)->setValue($value)->setLocale($locale)->setScore($score);
        $this->setAttributes($this->getAttributes() ? array_merge($this->getAttributes(), [$attribute]) : [$attribute]);

        return $this;
    }
}
