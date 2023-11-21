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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Doctrine\ORM\Mapping as ORM;

trait SearchableTrait
{
    /**
     * @ORM\Column(name="searchable", type="boolean", nullable=false, options={"default"=false})
     */
    #[ORM\Column(name: 'searchable', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $searchable = false;

    /**
     * @ORM\Column(name="filterable", type="boolean", nullable=false, options={"default"=false})
     */
    #[ORM\Column(name: 'filterable', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $filterable = false;

    /**
     * @ORM\Column(name="search_weight", type="smallint", nullable=false, options={"default"=1, "unsigned"=true})
     */
    #[ORM\Column(name: 'search_weight', type: 'smallint', nullable: false, options: ['default' => 1, 'unsigned' => true])]
    protected int $searchWeight = 1;

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function setSearchable(bool $searchable): void
    {
        $this->searchable = $searchable;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }

    public function getSearchWeight(): int
    {
        return $this->searchWeight;
    }

    public function setSearchWeight(int $searchWeight): void
    {
        $this->searchWeight = $searchWeight;
    }
}
