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

namespace MonsieurBiz\SyliusSearchPlugin\Search;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

class Response implements ResponseInterface
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getIterator()
    {
        return $this->createPaginator();
    }

    public function count()
    {
        return $this->createPaginator()->getNbResults();
    }

    public function getFilters(): array
    {
        return [];
    }

    private function createPaginator(): Pagerfanta
    {
        return new Pagerfanta($this->adapter);
    }
}
