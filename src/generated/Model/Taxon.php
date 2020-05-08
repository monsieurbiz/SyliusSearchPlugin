<?php

namespace MonsieurBiz\SyliusSearchPlugin\generated\Model;

class Taxon
{
    /**
     * 
     *
     * @var string|null
     */
    protected $name;
    /**
     * 
     *
     * @var string|null
     */
    protected $code;
    /**
     * 
     *
     * @var int|null
     */
    protected $position;
    /**
     * 
     *
     * @var int|null
     */
    protected $level;
    /**
     * 
     *
     * @var int|null
     */
    protected $productPosition;
    /**
     * 
     *
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * 
     *
     * @param string|null $name
     *
     * @return self
     */
    public function setName(?string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    /**
     * 
     *
     * @return string|null
     */
    public function getCode() : ?string
    {
        return $this->code;
    }
    /**
     * 
     *
     * @param string|null $code
     *
     * @return self
     */
    public function setCode(?string $code) : self
    {
        $this->code = $code;
        return $this;
    }
    /**
     * 
     *
     * @return int|null
     */
    public function getPosition() : ?int
    {
        return $this->position;
    }
    /**
     * 
     *
     * @param int|null $position
     *
     * @return self
     */
    public function setPosition(?int $position) : self
    {
        $this->position = $position;
        return $this;
    }
    /**
     * 
     *
     * @return int|null
     */
    public function getLevel() : ?int
    {
        return $this->level;
    }
    /**
     * 
     *
     * @param int|null $level
     *
     * @return self
     */
    public function setLevel(?int $level) : self
    {
        $this->level = $level;
        return $this;
    }
    /**
     * 
     *
     * @return int|null
     */
    public function getProductPosition() : ?int
    {
        return $this->productPosition;
    }
    /**
     * 
     *
     * @param int|null $productPosition
     *
     * @return self
     */
    public function setProductPosition(?int $productPosition) : self
    {
        $this->productPosition = $productPosition;
        return $this;
    }
}