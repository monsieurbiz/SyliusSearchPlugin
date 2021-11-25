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
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Pagerfanta\Elastica\ElasticaAdapter;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Serializer\SerializerInterface;

class Search implements SearchInterface
{
    private SerializerInterface $serializer;
    private LocaleContextInterface $localeContext;

    public function __construct(SerializerInterface $serializer, LocaleContextInterface $localeContext)
    {
        $this->serializer = $serializer;
        $this->localeContext = $localeContext;
    }

    public function query(RequestConfiguration $requestConfiguration, RequestInterface $request): ResponseInterface
    {
        $indexName = $this->getIndexName($request->getDocumentable(), $this->localeContext->getLocaleCode());
        $factory = new Factory([
            Factory::CONFIG_INDEX_CLASS_MAPPING => [
                $indexName => $request->getDocumentable()->getTargetClass(),
            ],
            Factory::CONFIG_SERIALIZER => $this->serializer,
        ]);
        $client = $factory->buildClient();

        return new Response($requestConfiguration, new ElasticaAdapter($client->getIndex($indexName), $request->getQuery()));
    }

    private function getIndexName(DocumentableInterface $documentable, ?string $locale = null): string
    {
        return $documentable->getIndexCode() . strtolower((null !== $locale && $documentable->isTranslatable()) ? '_' . $locale : '');
    }
}
