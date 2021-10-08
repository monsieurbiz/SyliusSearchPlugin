<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductTaxon
{
    /**
     * 
     *
     * @var Taxon
     */
    protected $taxon;
    /**
     * 
     *
     * @var null|int
     */
    protected $position;
    /**
     * 
     *
     * @return Taxon
     */
    public function getTaxon() : Taxon
    {
        return $this->taxon;
    }
    /**
     * 
     *
     * @param Taxon $taxon
     *
     * @return self
     */
    public function setTaxon(Taxon $taxon) : self
    {
        $this->taxon = $taxon;
        return $this;
    }
    /**
     * 
     *
     * @return null|int
     */
    public function getPosition() : ?int
    {
        return $this->position;
    }
    /**
     * 
     *
     * @param null|int $position
     *
     * @return self
     */
    public function setPosition(?int $position) : self
    {
        $this->position = $position;
        return $this;
    }
}