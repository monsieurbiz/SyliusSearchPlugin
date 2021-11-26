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

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;
use Pagerfanta\Pagerfanta;

interface ResponseInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array;

    public function getPaginator(): Pagerfanta;

    public function getDocumentable(): DocumentableInterface;
}
