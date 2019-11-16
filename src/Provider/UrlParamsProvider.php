<?php

declare(strict_types=1);


namespace Monsieurbiz\SyliusSearchPlugin\Provider;

class UrlParamsProvider
{
    /** @var string */
    private $path;

    /** @var array */
    private $params;

    /**
     * UrlParamsProvider constructor.
     * @param string $path
     * @param array $params
     */
    public function __construct(string $path, array $params)
    {
        $this->path = $path;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

}
