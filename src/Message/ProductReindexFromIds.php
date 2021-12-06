<?php

/*
 * This file is part of SyliusSearchPlugin.
 *
 * (c) Monsieur Biz
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Message;

class ProductReindexFromIds
{
    private array $productIds;

    public function __construct(array $productIds = [])
    {
        $this->productIds = $productIds;
    }

    public function getProductIds(): array
    {
        return array_unique($this->productIds);
    }

    public function addProductId(int $productIds): void
    {
        $this->productIds[] = $productIds;
    }
}
