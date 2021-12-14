<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductTaxonDTO
{
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