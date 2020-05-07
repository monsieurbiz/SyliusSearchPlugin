<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Provider;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingConfigFileException;
use MonsieurBiz\SyliusSearchPlugin\Exception\ReadFileException;
use MonsieurBiz\SyliusSearchPlugin\Model\Config\FilesConfig;

class SearchQueryProvider
{
    /** @var FilesConfig */
    private $filesConfig;

    /**
     * SearchQueryProvider constructor.
     * @param array $files
     * @throws MissingConfigFileException
     */
    public function __construct(array $files)
    {
        $this->filesConfig = new FilesConfig($files);
    }

    /**
     * Get search query
     *
     * @return string
     * @throws ReadFileException
     */
    public function getSearchQuery()
    {
        return $this->getQuery($this->filesConfig->getSearchPath());
    }

    /**
     * Get instant query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getInstantQuery()
    {
        return $this->getQuery($this->filesConfig->getInstantPath());
    }

    /**
     * Get taxon query
     *
     * @return false|string
     * @throws ReadFileException
     */
    public function getTaxonQuery()
    {
        return $this->getQuery($this->filesConfig->getTaxonPath());
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
