<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

class RangeFilter
{
    /**
     * @var string
     */
    private $label;

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
     * @param string $label
     * @param int $min
     * @param int $max
     */
    public function __construct(string $label, int $min, int $max)
    {
        $this->label = $label;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
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
