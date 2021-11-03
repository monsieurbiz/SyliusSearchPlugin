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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

class RangeFilter
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
     * @var string
     */
    private $maxLabel;

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * Filter constructor.
     */
    public function __construct(string $code, string $label, string $minLabel, string $maxLabel, int $min, int $max)
    {
        $this->code = $code;
        $this->label = $label;
        $this->minLabel = $minLabel;
        $this->maxLabel = $maxLabel;
        $this->min = $min;
        $this->max = $max;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getMinLabel(): string
    {
        return $this->minLabel;
    }

    public function getMaxLabel(): string
    {
        return $this->maxLabel;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }
}
