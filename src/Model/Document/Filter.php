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

class Filter
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

    /**
     * Filter constructor.
     *
     * @param string $code
     * @param string $label
     * @param int $count
     */
    public function __construct(string $code, string $label, int $count)
    {
        $this->code = $code;
        $this->label = $label;
        $this->count = $count;
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

    /**
     * @param $value
     * @param $count
     */
    public function addValue($value, $count): void
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
