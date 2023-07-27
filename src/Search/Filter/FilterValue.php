<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Search\Filter;

use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;

class FilterValue
{
    private string $label;

    private int $count;

    private string $value;

    private bool $isApplied;

    /**
     * Filter constructor.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $label, int $count, string $value = null, bool $isApplied = false)
    {
        $this->value = $value ?? $label;
        $this->label = $label;
        $this->count = $count;
        $this->isApplied = $isApplied;
    }

    public function getSlug(): string
    {
        return SlugHelper::toSlug($this->value);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function isApplied(): bool
    {
        return $this->isApplied;
    }
}
