<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ChannelDTO
{
    /**
     * 
     *
     * @var string
     */
    protected $code;
    /**
     * 
     *
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }
    /**
     * 
     *
     * @param string $code
     *
     * @return self
     */
    public function setCode(string $code) : self
    {
        $this->code = $code;
        return $this;
    }
}