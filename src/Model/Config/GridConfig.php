<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Config;

class GridConfig
{
    /** @var int[] */
    private $taxonLimits;

    /** @var int[] */
    private $searchLimits;

    /** @var int */
    private $taxonDefaultLimit;

    /** @var int */
    private $searchDefaultLimit;

    /** @var int */
    private $instantDefaultLimit;

    /** @var string[] */
    private $taxonSorting;

    /** @var string[] */
    private $searchSorting;

    /** @var string[] */
    private $attributeFilters;

    /** @var string[] */
    private $optionFilters;

    public function __construct(array $gridConfig)
    {
        $this->taxonLimits = $gridConfig['limits']['taxon'] ?? [];
        $this->searchLimits = $gridConfig['limits']['search'] ?? [];
        $this->taxonDefaultLimit = $gridConfig['default_limit']['taxon'] ?? 9;
        $this->searchDefaultLimit = $gridConfig['default_limit']['search'] ?? 9;
        $this->instantDefaultLimit = $gridConfig['default_limit']['instant'] ?? 10;
        $this->taxonSorting = $gridConfig['sorting']['taxon'] ?? [];
        $this->searchSorting = $gridConfig['sorting']['search'] ?? [];
        $this->attributeFilters = $gridConfig['filters']['attributes'] ?? [];
        $this->optionFilters = $gridConfig['filters']['options'] ?? [];
    }

    /**
     * @return int[]
     */
    public function getTaxonLimits(): array
    {
        return $this->taxonLimits;
    }

    /**
     * @return int[]
     */
    public function getSearchLimits(): array
    {
        return $this->searchLimits;
    }

    /**
     * @return int
     */
    public function getTaxonDefaultLimit(): int
    {
        return $this->taxonDefaultLimit;
    }

    /**
     * @return int
     */
    public function getSearchDefaultLimit(): int
    {
        return $this->searchDefaultLimit;
    }

    /**
     * @return int
     */
    public function getInstantDefaultLimit(): int
    {
        return $this->instantDefaultLimit;
    }

    /**
     * @return string[]
     */
    public function getTaxonSorting(): array
    {
        return $this->taxonSorting;
    }

    /**
     * @return string[]
     */
    public function getSearchSorting(): array
    {
        return $this->searchSorting;
    }

    /**
     * @return string[]
     */
    public function getAttributeFilters(): array
    {
        return $this->attributeFilters;
    }

    /**
     * @return string[]
     */
    public function getOptionFilters(): array
    {
        return $this->optionFilters;
    }
}

