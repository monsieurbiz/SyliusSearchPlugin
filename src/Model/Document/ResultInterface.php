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

interface ResultInterface
{
    /**
     * Document ID in elasticsearch.
     *
     * @throws MissingParamException
     *
     * @return string
     */
    public function getUniqId(): string;


    /**
     * @param string $code
     *
     * @return Attributes
     */
    public function getAttribute(string $code): ?Attributes;

    /**
     * @param string $channelCode
     * @param string $currencyCode
     *
     * @throws MissingPriceException
     *
     * @return Price|null
     */
    public function getPriceByChannelAndCurrency(string $channelCode, string $currencyCode): ?Price;

    /**
     * @param string $channelCode
     * @param string $currencyCode
     *
     * @throws MissingPriceException
     *
     * @return Price|null
     */
    public function getOriginalPriceByChannelAndCurrency(string $channelCode, string $currencyCode): ?Price;

    /**
     * @throws MissingLocaleException
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     *
     * @return UrlParamsProvider
     */
    public function getUrlParams(): UrlParamsProvider;

    /**
     * @param string $channel
     *
     * @return ResultInterface
     */
    public function addChannel(string $channel): self;

    /**
     * @param string $code
     * @param string $name
     * @param int $position
     * @param int $level
     * @param int $productPosition
     *
     * @return ResultInterface
     */
    public function addTaxon(string $code, string $name, int $position, int $level, int $productPosition): self;

    /**
     * @param string $channel
     * @param string $currency
     * @param int $value
     *
     * @return ResultInterface
     */
    public function addPrice(string $channel, string $currency, int $value): self;

    /**
     * @param string $channel
     * @param string $currency
     * @param int $value
     *
     * @return ResultInterface
     */
    public function addOriginalPrice(string $channel, string $currency, int $value): self;

    /**
     * @param string $code
     * @param string $name
     * @param array $value
     * @param string $locale
     * @param int $score
     *
     * @return ResultInterface
     */
    public function addAttribute(string $code, string $name, array $value, string $locale, int $score): self;
}
