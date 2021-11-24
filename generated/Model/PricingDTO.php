<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class PricingDTO
{
    /**
     * 
     *
     * @var string
     */
    protected $channelCode;
    /**
     * 
     *
     * @var null|int
     */
    protected $price;
    /**
     * 
     *
     * @var null|int
     */
    protected $originalPrice;
    /**
     * 
     *
     * @var bool
     */
    protected $priceReduced;
    /**
     * 
     *
     * @return string
     */
    public function getChannelCode() : string
    {
        return $this->channelCode;
    }
    /**
     * 
     *
     * @param string $channelCode
     *
     * @return self
     */
    public function setChannelCode(string $channelCode) : self
    {
        $this->channelCode = $channelCode;
        return $this;
    }
    /**
     * 
     *
     * @return null|int
     */
    public function getPrice() : ?int
    {
        return $this->price;
    }
    /**
     * 
     *
     * @param null|int $price
     *
     * @return self
     */
    public function setPrice(?int $price) : self
    {
        $this->price = $price;
        return $this;
    }
    /**
     * 
     *
     * @return null|int
     */
    public function getOriginalPrice() : ?int
    {
        return $this->originalPrice;
    }
    /**
     * 
     *
     * @param null|int $originalPrice
     *
     * @return self
     */
    public function setOriginalPrice(?int $originalPrice) : self
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }
    /**
     * 
     *
     * @return bool
     */
    public function getPriceReduced() : bool
    {
        return $this->priceReduced;
    }
    /**
     * 
     *
     * @param bool $priceReduced
     *
     * @return self
     */
    public function setPriceReduced(bool $priceReduced) : self
    {
        $this->priceReduced = $priceReduced;
        return $this;
    }
}