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
     * @var int
     */
    private $count;

    /**
     * @var FilterValue[]
     */
    private $values;

    /**
     * Filter constructor.
     * @param string $label
     */
    public function __construct(string $label)
    {
        $this->label = $label;
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
    public function getCount(): int
    {
        return $this->count;
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
}
