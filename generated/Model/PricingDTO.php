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

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class PricingDTO
{
    /**
     * @var string
     */
    protected $channelCode;

    /**
     * @var int|null
     */
    protected $price;

    /**
     * @var int|null
     */
    protected $originalPrice;

    /**
     * @var bool
     */
    protected $priceReduced;

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    public function setChannelCode(string $channelCode): self
    {
        $this->channelCode = $channelCode;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOriginalPrice(): ?int
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?int $originalPrice): self
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    public function getPriceReduced(): bool
    {
        return $this->priceReduced;
    }

    public function setPriceReduced(bool $priceReduced): self
    {
        $this->priceReduced = $priceReduced;

        return $this;
    }
}
