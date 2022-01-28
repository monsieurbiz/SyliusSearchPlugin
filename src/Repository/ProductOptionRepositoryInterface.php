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
