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

use Countable;
use IteratorAggregate;
use JoliCode\Elastically\Result;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @extends IteratorAggregate<int, Result>
 */
interface ResponseInterface extends IteratorAggregate, Countable
{
    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array;

    /**
     * @return Pagerfanta<Result>
     */
    public function getPaginator(): Pagerfanta;

    public function getDocumentable(): DocumentableInterface;
}
