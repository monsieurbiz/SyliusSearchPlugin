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

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;

class RangeFilter implements FilterInterface
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
     * @var string
     */
    private $minLabel;

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    private array $values = [];
    private RequestConfiguration $requestConfiguration;

    /**
     * Filter constructor.
     */
    public function __construct(RequestConfiguration $requestConfiguration, string $code, string $label, string $minLabel, string $maxLabel, int $min, int $max)
    {
        $this->requestConfiguration = $requestConfiguration;
        $this->code = $code;
        $this->label = $label;
        $this->minLabel = $minLabel;
        $this->min = $min;
        $this->max = $max;

        $this->addValue($minLabel, 0, (string) $min);
        $this->addValue($maxLabel, 0, (string) $max);
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

    public function addValue(string $label, int $count, ?string $value = null): void
    {
        $currentValueType = $this->getValueType($label);
        $currentValues = $this->getCurrentValues();
        $isApplied = \array_key_exists($currentValueType, $currentValues);
        $value = $isApplied ? $currentValues[$currentValueType] : $value;

        $this->values[] = new FilterValue($label, $count, $value, $isApplied);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getType(): string
    {
        return 'range';
    }

    public function getAppliedValues(): array
    {
        return array_filter($this->values, function(FilterValue $filterValue): bool {
            return $filterValue->isApplied();
        });
    }

    public function getDefaultValue(string $type): int
    {
        if ('min' == $type) {
            return $this->min;
        }

        return $this->max;
    }

    public function getValueType($valueLabel): string
    {
        if ($valueLabel == $this->minLabel) {
            return 'min';
        }

        return 'max';
    }

    protected function getCurrentValues(): array
    {
        $appliedFilters = $this->requestConfiguration->getAppliedFilters();
        $appliedFilters = $appliedFilters[$this->getType()] ?? $appliedFilters[$this->getCode()];

        return $appliedFilters[$this->getCode()] ?? $appliedFilters;
    }
}
