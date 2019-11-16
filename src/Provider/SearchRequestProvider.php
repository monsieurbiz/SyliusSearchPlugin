<?php

declare(strict_types=1);

namespace Monsieurbiz\SyliusSearchPlugin\Provider;

use Monsieurbiz\SyliusSearchPlugin\Exception\ReadFileException;

class SearchRequestProvider
{
    /** @var string */
    private $searchPath;

    /** @var string */
    private $instantPath;

    /**
     * SearchRequestProvider constructor.
     * @param string $searchPath
     * @param string $instantPath
     */
    public function __construct(string $searchPath, string $instantPath)
    {
        $this->searchPath = $searchPath;
        $this->instantPath = $instantPath;
    }

    /**
     * Get search JSON query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getSearchJson()
    {
        $json = @file_get_contents($this->searchPath);
        if ($json === false) {
            throw new ReadFileException(sprintf('Error while opening file "%s".', $this->searchPath));
        }
        return $json;
    }

    /**
     * Get instant JSON query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getInstantJson()
    {
        $json = @file_get_contents($this->instantPath);
        if ($json === false) {
            throw new ReadFileException(sprintf('Error while opening file "%s".', $this->searchPath));
        }
        return $json;
    }
}
