<?php

namespace MonsieurBiz\SyliusSearchPlugin\generated\Model;

class Taxon
{
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
}