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
     *
     * @param string $code
     * @param string $label
     * @param string $minLabel
     * @param string $maxLabel
     * @param int $min
     * @param int $max
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
     * @return string
     */
    public function getMinLabel(): string
    {
        return $this->minLabel;
    }

    /**
     * @return string
     */
    public function getMaxLabel(): string
    {
        return $this->maxLabel;
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }
}
