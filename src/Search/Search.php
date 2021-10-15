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
use Pagerfanta\Elastica\ElasticaAdapter;
use Symfony\Component\Serializer\SerializerInterface;

class Search implements SearchInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function query(RequestInterface $request): ResponseInterface
    {
        $factory = new Factory([
            Factory::CONFIG_INDEX_CLASS_MAPPING => [
                $this->getIndexName($request->getDocumentable(), 'fr_FR') => $request->getDocumentable()->getTargetClass(),
            ],
            Factory::CONFIG_SERIALIZER => $this->serializer,
        ]);
        $client = $factory->buildClient();

        return new Response(new ElasticaAdapter($client->getIndex('monsieurbiz_product_fr_fr'), $request->getQuery()));
    }

    private function getIndexName(DocumentableInterface $documentable, ?string $locale = null): string
    {
        return $documentable->getIndexCode() . strtolower(null !== $locale ? '_' . $locale : '');
    }
}
