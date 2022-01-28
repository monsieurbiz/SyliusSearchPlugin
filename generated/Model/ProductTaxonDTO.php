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

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductTaxonDTO
{
    /**
     * @var TaxonDTO
     */
    protected $taxon;

    /**
     * @var int|null
     */
    protected $position;

    public function getTaxon(): TaxonDTO
    {
        return $this->taxon;
    }

    public function setTaxon(TaxonDTO $taxon): self
    {
        $this->taxon = $taxon;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
