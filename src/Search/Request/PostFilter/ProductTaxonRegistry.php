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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter;

use Sylius\Component\Registry\ServiceRegistry;

final class ProductTaxonRegistry extends ServiceRegistry implements PostFilterRegistryInterface
{
    public function __construct(array $queryFilters = [])
    {
        parent::__construct(PostFilterInterface::class, 'monsieurbiz.search');

        foreach ($queryFilters as $queryFilter) {
            $this->register(\get_class($queryFilter), $queryFilter);
        }
    }
}
