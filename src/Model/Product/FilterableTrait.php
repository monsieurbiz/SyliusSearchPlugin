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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Doctrine\ORM\Mapping as ORM;

trait FilterableTrait
{
    /**
     * @var bool
     * @ORM\Column(name="filterable", type="boolean", nullable=false, options={"default"=true})
     */
    protected $filterable = true;

    /**
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * @param bool $filterable
     */
    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }
}
