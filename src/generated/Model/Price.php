<?php

namespace MonsieurBiz\SyliusSearchPlugin\generated\Model;

class Price
{
    /**
     * 
     *
     * @var string|null
     */
    protected $channel;
    /**
     * 
     *
     * @var string|null
     */
    protected $currency;
    /**
     * 
     *
     * @var int|null
     */
    protected $value;
    /**
     * 
     *
     * @return string|null
     */
    public function getChannel() : ?string
    {
        return $this->channel;
    }
    /**
     * 
     *
     * @param string|null $channel
     *
     * @return self
     */
    public function setChannel(?string $channel) : self
    {
        $this->channel = $channel;
        return $this;
    }
    /**
     * 
     *
     * @return string|null
     */
    public function getCurrency() : ?string
    {
        return $this->currency;
    }
    /**
     * 
     *
     * @param string|null $currency
     *
     * @return self
     */
    public function setCurrency(?string $currency) : self
    {
        $this->currency = $currency;
        return $this;
    }
    /**
     * 
     *
     * @return int|null
     */
    public function getValue() : ?int
    {
        return $this->value;
    }
    /**
     * 
     *
     * @param int|null $value
     *
     * @return self
     */
    public function setValue(?int $value) : self
    {
        $this->value = $value;
        return $this;
    }
}