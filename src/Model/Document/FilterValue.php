<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Document;

class FilterValue
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
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
