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

use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;

class FilterValue
{
    /**
     * @var string
     */
    private $slug;

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
     */
    public function __construct(string $label, int $count)
    {
        $this->slug = SlugHelper::toSlug($label);
        $this->label = $label;
        $this->count = $count;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
