<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ImageDTO
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
     * @var null|string
     */
    protected $path;
    /**
     * 
     *
     * @var null|string
     */
    protected $type;
    /**
     * 
     *
     * @return null|string
     */
    public function getPath() : ?string
    {
        return $this->path;
    }
    /**
     * 
     *
     * @param null|string $path
     *
     * @return self
     */
    public function setPath(?string $path) : self
    {
        $this->initialized['path'] = true;
        $this->path = $path;
        return $this;
    }
    /**
     * 
     *
     * @return null|string
     */
    public function getType() : ?string
    {
        return $this->type;
    }
    /**
     * 
     *
     * @param null|string $type
     *
     * @return self
     */
    public function setType(?string $type) : self
    {
        $this->initialized['type'] = true;
        $this->type = $type;
        return $this;
    }
}