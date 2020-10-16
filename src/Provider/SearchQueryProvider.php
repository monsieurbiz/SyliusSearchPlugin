<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     *
     * @param array $files
     *
     * @throws MissingConfigFileException
     */
    public function __construct(array $files)
    {
        $this->filesConfig = new FilesConfig($files);
    }

    /**
     * Get search query.
     *
     * @throws ReadFileException
     *
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->getQuery($this->filesConfig->getSearchPath());
    }

    /**
     * Get instant query.
     *
     * @throws ReadFileException
     *
     * @return false|string
     */
    public function getInstantQuery()
    {
        return $this->getQuery($this->filesConfig->getInstantPath());
    }

    /**
     * Get taxon query.
     *
     * @throws ReadFileException
     *
     * @return false|string
     */
    public function getTaxonQuery()
    {
        return $this->getQuery($this->filesConfig->getTaxonPath());
    }

    /**
     * Get content from file.
     *
     * @param $path
     *
     * @throws ReadFileException
     *
     * @return false|string
     */
    private function getQuery($path)
    {
        $query = @file_get_contents($path);
        if (false === $query) {
            throw new ReadFileException(sprintf('Error while opening file "%s".', $path));
        }

        return $query;
    }
}
