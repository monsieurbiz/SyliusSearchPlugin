<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

class Filter
{
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

    /**
     * Filter constructor.
     * @param string $label
     * @param int $count
     */
    public function __construct(string $label, int $count)
    {
        $this->label = $label;
        $this->count = $count;
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

    /**
     * @param $value
     * @param $count
     */
    public function addValue($value, $count)
    {
        $this->values[] = new FilterValue($value, $count);
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
