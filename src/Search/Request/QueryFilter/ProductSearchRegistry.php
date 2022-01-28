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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter;

use Sylius\Component\Registry\ServiceRegistry;

final class ProductSearchRegistry extends ServiceRegistry implements QueryFilterRegistryInterface
{
    public function __construct(array $queryFilters = [])
    {
        parent::__construct(QueryFilterInterface::class, 'monsieurbiz.search');

        foreach ($queryFilters as $queryFilter) {
            $this->register(\get_class($queryFilter), $queryFilter);
        }
    }
}
