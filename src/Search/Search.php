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

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestHandler;
use Pagerfanta\Elastica\ElasticaAdapter;
use Sylius\Component\Locale\Context\LocaleContextInterface;

class Search implements SearchInterface
{
    private LocaleContextInterface $localeContext;
    private RequestHandler $requestHandler;
    private ClientFactory $clientFactory;
    private ResponseFactory $responseFactory;

    public function __construct(
        ClientFactory $clientFactory,
        LocaleContextInterface $localeContext,
        RequestHandler $requestHandler,
        ResponseFactory $responseFactory
    ) {
        $this->localeContext = $localeContext;
        $this->requestHandler = $requestHandler;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws UnknownRequestTypeException
     */
    public function search(RequestConfiguration $requestConfiguration): ResponseInterface
    {
        $request = $this->requestHandler->getRequest($requestConfiguration);

        $indexName = $this->clientFactory->getIndexName($request->getDocumentable(), $this->localeContext->getLocaleCode());
        $client = $this->clientFactory->getClient($request->getDocumentable(), $this->localeContext->getLocaleCode());

        return $this->responseFactory->build(
            $requestConfiguration,
            new ElasticaAdapter($client->getIndex($indexName), $request->getQuery()),
            $request->getDocumentable()
        );
    }
}
