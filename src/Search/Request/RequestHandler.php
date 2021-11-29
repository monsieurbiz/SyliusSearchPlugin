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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use Sylius\Component\Registry\ServiceRegistryInterface;

class RequestHandler
{
    private ServiceRegistryInterface $searchRequestsRegistry;

    public function __construct(ServiceRegistryInterface $searchRequestsRegistry)
    {
        $this->searchRequestsRegistry = $searchRequestsRegistry;
    }

    public function getRequest(RequestConfiguration $requestConfiguration): RequestInterface
    {
        /** @var RequestInterface $request */
        foreach ($this->searchRequestsRegistry->all() as $request) {
            if ($request->supports($requestConfiguration->getType(), $requestConfiguration->getDocumentType())) {
                $request->setConfiguration($requestConfiguration);

                return $request;
            }
        }

        throw new \Exception('Unknow request type'); // TODO
    }
}
