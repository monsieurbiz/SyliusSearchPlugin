<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Entity\Product;

interface FilterableInterface
{
    public function isFilterable(): bool;
    public function setFilterable(bool $filterable): void;
}
