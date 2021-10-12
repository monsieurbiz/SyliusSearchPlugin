<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductAttribute
{
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