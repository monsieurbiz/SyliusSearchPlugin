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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;

class RequestHandler
{
    private iterable $searchRequests;

    public function __construct(iterable $searchRequests)
    {
        $this->searchRequests = $searchRequests;
    }

    /**
     * @throws UnknownRequestTypeException
     */
    public function getRequest(RequestConfiguration $requestConfiguration): RequestInterface
    {
        /** @var RequestInterface $request */
        foreach ($this->searchRequests as $request) {
            if ($request->supports($requestConfiguration->getType(), $requestConfiguration->getDocumentType())) {
                $request->setConfiguration($requestConfiguration);

                return $request;
            }
        }

        throw new UnknownRequestTypeException();
    }
}
