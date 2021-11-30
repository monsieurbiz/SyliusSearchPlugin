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

use JoliCode\Elastically\Factory;
use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestHandler;
use Pagerfanta\Elastica\ElasticaAdapter;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Serializer\SerializerInterface;

class Search implements SearchInterface
{
    private SerializerInterface $serializer;
    private LocaleContextInterface $localeContext;
    private RequestHandler $requestHandler;

    public function __construct(SerializerInterface $serializer, LocaleContextInterface $localeContext, RequestHandler $requestHandler)
    {
        $this->serializer = $serializer;
        $this->localeContext = $localeContext;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @throws UnknownRequestTypeException
     */
    public function search(RequestConfiguration $requestConfiguration): ResponseInterface
    {
        $request = $this->requestHandler->getRequest($requestConfiguration);

        $indexName = $this->getIndexName($request->getDocumentable(), $this->localeContext->getLocaleCode());
        $factory = new Factory([
            Factory::CONFIG_INDEX_CLASS_MAPPING => [
                $indexName => $request->getDocumentable()->getTargetClass(),
            ],
            Factory::CONFIG_SERIALIZER => $this->serializer,
        ]);
        $client = $factory->buildClient();

        return new Response(
            $requestConfiguration,
            new ElasticaAdapter($client->getIndex($indexName), $request->getQuery()),
            $request->getDocumentable()
        );
    }

    private function getIndexName(DocumentableInterface $documentable, ?string $locale = null): string
    {
        return $documentable->getIndexCode() . strtolower((null !== $locale && $documentable->isTranslatable()) ? '_' . $locale : '');
    }
}
