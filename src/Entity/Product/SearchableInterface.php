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

namespace MonsieurBiz\SyliusSearchPlugin\Entity\Product;

interface SearchableInterface
{
    public function isSearchable(): bool;

    public function setSearchable(bool $searchable): void;

    public function isFilterable(): bool;

    public function setFilterable(bool $filterable): void;

    public function getSearchWeight(): int;

    public function setSearchWeight(int $searchWeight): void;
}
