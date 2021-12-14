<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductAttributeDTO
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
     * @var string
     */
    protected $name;
    /**
     * 
     *
     * @var null|mixed
     */
    protected $value;
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
    /**
     * 
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * 
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    /**
     * 
     *
     * @return null|mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * 
     *
     * @param null|mixed $value
     *
     * @return self
     */
    public function setValue($value) : self
    {
        $this->value = $value;
        return $this;
    }
}