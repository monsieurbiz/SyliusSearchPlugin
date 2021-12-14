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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Filter;

use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;

class Filter implements FilterInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $label;

    /**
     * @var FilterValue[]
     */
    private $values = [];

    /**
     * @var int
     */
    private $count;

    private string $type;
    private RequestConfiguration $requestConfiguration;

    /**
     * Filter constructor.
     */
    public function __construct(RequestConfiguration $requestConfiguration, string $code, string $label, int $count, string $type = '')
    {
        $this->code = $code;
        $this->label = $label;
        $this->count = $count;
        $this->type = $type;
        $this->requestConfiguration = $requestConfiguration;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return FilterValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function addValue(string $label, int $count, ?string $value = null): void
    {
        $this->values[] = new FilterValue(
            $label,
            $count,
            $value,
            \in_array(SlugHelper::toSlug($value ?? $label), $this->getCurrentValues(), true)
        );
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAppliedValues(): array
    {
        return array_filter($this->getValues(), function(FilterValue $filterValue): bool {
            return $filterValue->isApplied();
        });
    }

    protected function getCurrentValues(): array
    {
        $appliedFilters = $this->requestConfiguration->getAppliedFilters();
        $appliedFilters = $appliedFilters[$this->getType()] ?? $appliedFilters[$this->getCode()];

        return $appliedFilters[$this->getCode()] ?? $appliedFilters;
    }
}
