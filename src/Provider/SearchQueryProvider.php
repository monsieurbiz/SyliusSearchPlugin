<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Provider;

use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;

class SearchQueryProvider
{
    /** @var string */
    private $searchPath;

    /** @var string */
    private $instantPath;

    /** @var string */
    private $taxonPath;

    /**
     * SearchQueryProvider constructor.
     * @param string $searchPath
     * @param string $instantPath
     * @param string $taxonPath
     */
    public function __construct(string $searchPath, string $instantPath, string $taxonPath)
    {
        $this->searchPath = $searchPath;
        $this->instantPath = $instantPath;
        $this->taxonPath = $taxonPath;
    }

    /**
     * Get search query
     *
     * @return string
     * @throws ReadFileException
     */
    public function getSearchQuery()
    {
        return $this->getQuery($this->searchPath);
    }

    /**
     * Get instant query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getInstantQuery()
    {
        return $this->getQuery($this->instantPath);
    }

    /**
     * Get taxon query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getTaxonQuery()
    {
        return $this->getQuery($this->taxonPath);
    }

    /**
     * Get content from file
     *
     * @param $path
     * @return false|string
     * @throws ReadFileException
     */
    private function getQuery($path)
    {
        $query = @file_get_contents($path);
        if ($query === false) {
            throw new ReadFileException(sprintf('Error while opening file "%s".', $path));
        }
        return $query;
    }
}
