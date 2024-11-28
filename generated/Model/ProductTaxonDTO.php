<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductTaxonDTO
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
     * @var TaxonDTO
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
     * @return TaxonDTO
     */
    public function getTaxon() : TaxonDTO
    {
        return $this->taxon;
    }
    /**
     * 
     *
     * @param TaxonDTO $taxon
     *
     * @return self
     */
    public function setTaxon(TaxonDTO $taxon) : self
    {
        $this->initialized['taxon'] = true;
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
        $this->initialized['position'] = true;
        $this->position = $position;
        return $this;
    }
}