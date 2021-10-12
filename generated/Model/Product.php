<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class Product
{
    /**
     * 
     *
     * @var int
     */
    protected $id;
    /**
     * 
     *
     * @var string
     */
    protected $code;
    /**
     * 
     *
     * @var bool
     */
    protected $enabled;
    /**
     * 
     *
     * @var string
     */
    protected $slug;
    /**
     * 
     *
     * @var string
     */
    protected $name;
    /**
     * 
     *
     * @var Taxon
     */
    protected $mainTaxon;
    /**
     * 
     *
     * @var ProductTaxon[]
     */
    protected $productTaxons;
    /**
     * 
     *
     * @var null|string
     */
    protected $description;
    /**
     * 
     *
     * @var null|Image[]
     */
    protected $images;
    /**
     * 
     *
     * @var Channel[]
     */
    protected $channels;
    /**
     * 
     *
     * @var ProductAttribute[]
     */
    protected $attributes;
    /**
     * 
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    /**
     * 
     *
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id) : self
    {
        $this->id = $id;
        return $this;
    }
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
     * @return bool
     */
    public function getEnabled() : bool
    {
        return $this->enabled;
    }
    /**
     * 
     *
     * @param bool $enabled
     *
     * @return self
     */
    public function setEnabled(bool $enabled) : self
    {
        $this->enabled = $enabled;
        return $this;
    }
    /**
     * 
     *
     * @return string
     */
    public function getSlug() : string
    {
        return $this->slug;
    }
    /**
     * 
     *
     * @param string $slug
     *
     * @return self
     */
    public function setSlug(string $slug) : self
    {
        $this->slug = $slug;
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
     * @return Taxon
     */
    public function getMainTaxon() : Taxon
    {
        return $this->mainTaxon;
    }
    /**
     * 
     *
     * @param Taxon $mainTaxon
     *
     * @return self
     */
    public function setMainTaxon(Taxon $mainTaxon) : self
    {
        $this->mainTaxon = $mainTaxon;
        return $this;
    }
    /**
     * 
     *
     * @return ProductTaxon[]
     */
    public function getProductTaxons() : array
    {
        return $this->productTaxons;
    }
    /**
     * 
     *
     * @param ProductTaxon[] $productTaxons
     *
     * @return self
     */
    public function setProductTaxons(array $productTaxons) : self
    {
        $this->productTaxons = $productTaxons;
        return $this;
    }
    /**
     * 
     *
     * @return null|string
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }
    /**
     * 
     *
     * @param null|string $description
     *
     * @return self
     */
    public function setDescription(?string $description) : self
    {
        $this->description = $description;
        return $this;
    }
    /**
     * 
     *
     * @return null|Image[]
     */
    public function getImages() : ?array
    {
        return $this->images;
    }
    /**
     * 
     *
     * @param null|Image[] $images
     *
     * @return self
     */
    public function setImages(?array $images) : self
    {
        $this->images = $images;
        return $this;
    }
    /**
     * 
     *
     * @return Channel[]
     */
    public function getChannels() : array
    {
        return $this->channels;
    }
    /**
     * 
     *
     * @param Channel[] $channels
     *
     * @return self
     */
    public function setChannels(array $channels) : self
    {
        $this->channels = $channels;
        return $this;
    }
    /**
     * 
     *
     * @return ProductAttribute[]
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }
    /**
     * 
     *
     * @param ProductAttribute[] $attributes
     *
     * @return self
     */
    public function setAttributes(array $attributes) : self
    {
        $this->attributes = $attributes;
        return $this;
    }
}