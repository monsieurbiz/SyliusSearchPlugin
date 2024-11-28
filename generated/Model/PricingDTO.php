<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class PricingDTO
{
    /**
     * @var array
     */
    protected $initialized = [];
    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }
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
        $this->initialized['channelCode'] = true;
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
        $this->initialized['price'] = true;
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
        $this->initialized['originalPrice'] = true;
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
        $this->initialized['priceReduced'] = true;
        $this->priceReduced = $priceReduced;
        return $this;
    }
}