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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Pagerfanta\Adapter\AdapterInterface;

class ResponseFactory
{
    private iterable $filterBuilders;

    public function __construct(iterable $filterBuilders)
    {
        $this->filterBuilders = $filterBuilders;
    }

    public function build(RequestConfiguration $requestConfiguration, AdapterInterface $adapter, DocumentableInterface $documentable): ResponseInterface
    {
        return new Response(
            $requestConfiguration,
            $adapter,
            $documentable,
            $this->filterBuilders
        );
    }
}
