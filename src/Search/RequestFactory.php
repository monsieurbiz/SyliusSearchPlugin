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

use Sylius\Component\Registry\ServiceRegistryInterface;

class RequestFactory
{
    private ServiceRegistryInterface $searchRequestsRegistry;

    public function __construct(ServiceRegistryInterface $searchRequestsRegistry)
    {
        $this->searchRequestsRegistry = $searchRequestsRegistry;
    }

    public function create(string $type, string $documentType): RequestInterface
    {
        /** @var RequestInterface $request */
        foreach ($this->searchRequestsRegistry->all() as $request) {
            if ($request->supports($type, $documentType)) {
                return $request;
            }
        }

        throw new \Exception('Unknow request type'); // TODO
    }
}
