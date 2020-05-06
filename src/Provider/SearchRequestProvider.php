<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Provider;

use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;

class SearchRequestProvider
{
    /** @var string */
    private $searchPath;

    /** @var string */
    private $instantPath;

    /** @var string */
    private $taxonPath;

    /**
     * SearchRequestProvider constructor.
     * @param string $searchPath
     * @param string $instantPath
     */
    public function __construct(string $searchPath, string $instantPath, string $taxonPath)
    {
        $this->searchPath = $searchPath;
        $this->instantPath = $instantPath;
        $this->taxonPath = $taxonPath;
    }

    /**
     * Get search JSON query
     *
     * @return string
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

    /**
     * Get taxon JSON query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getTaxonJson()
    {
        $json = @file_get_contents($this->taxonPath);
        if ($json === false) {
            throw new ReadFileException(sprintf('Error while opening file "%s".', $this->searchPath));
        }
        return $json;
    }
}
