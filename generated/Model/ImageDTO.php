<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ImageDTO
{
    /**
     * 
     *
     * @var null|string
     */
    protected $path;
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
        $this->path = $path;
        return $this;
    }
}