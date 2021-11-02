<?php

namespace MonsieurBiz\SyliusSearchPlugin\Repository;

use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;

interface ProductOptionRepositoryInterface
{
    /**
     * @return ProductOptionInterface[]&SearchableInterface[]
     */
    public function findIsSearchableOrFilterable(): array;
}
