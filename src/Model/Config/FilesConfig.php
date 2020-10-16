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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Config;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingConfigFileException;

class FilesConfig
{
    /** @var string */
    private $searchPath;

    /** @var string */
    private $instantPath;

    /** @var string */
    private $taxonPath;

    public function __construct(array $files)
    {
        if (!isset($files['search']) || !isset($files['instant']) || !isset($files['taxon'])) {
            throw new MissingConfigFileException('You need to have 3 config files : search, instant and taxon');
        }
        $this->searchPath = $files['search'];
        $this->instantPath = $files['instant'];
        $this->taxonPath = $files['taxon'];
    }

    /**
     * @return string
     */
    public function getSearchPath(): string
    {
        return $this->searchPath;
    }

    /**
     * @return string
     */
    public function getInstantPath(): string
    {
        return $this->instantPath;
    }

    /**
     * @return string
     */
    public function getTaxonPath(): string
    {
        return $this->taxonPath;
    }
}
