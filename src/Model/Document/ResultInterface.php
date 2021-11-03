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
use MonsieurBiz\SyliusSearchPlugin\Provider\UrlParamsProvider;

interface ResultInterface
{
    /**
     * Document ID in elasticsearch.
     *
     * @throws MissingParamException
     */
    public function getUniqId(): string;

    /**
     * @return Attributes
     */
    public function getAttribute(string $code): ?Attributes;

    /**
     * @throws MissingPriceException
     */
    public function getPriceByChannelAndCurrency(string $channelCode, string $currencyCode): ?Price;

    /**
     * @throws MissingPriceException
     */
    public function getOriginalPriceByChannelAndCurrency(string $channelCode, string $currencyCode): ?Price;

    /**
     * @throws MissingLocaleException
     */
    public function getLocale(): string;

    /**
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     */
    public function getUrlParams(): UrlParamsProvider;

    /**
     * @return ResultInterface
     */
    public function addChannel(string $channel): self;

    /**
     * @return ResultInterface
     */
    public function addTaxon(string $code, string $name, int $position, int $level, int $productPosition): self;

    /**
     * @return ResultInterface
     */
    public function addPrice(string $channel, string $currency, int $value): self;

    /**
     * @return ResultInterface
     */
    public function addOriginalPrice(string $channel, string $currency, int $value): self;

    /**
     * @return ResultInterface
     */
    public function addAttribute(string $code, string $name, array $value, string $locale, int $score): self;
}
