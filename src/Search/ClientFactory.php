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

use JoliCode\Elastically\Client;
use JoliCode\Elastically\Factory;
use JoliCode\Elastically\IndexBuilder;
use JoliCode\Elastically\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ClientFactory
{
    private array $config;
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer, array $config = [])
    {
        $this->config = $config;
        $this->serializer = $serializer;
    }

    public function getClient(DocumentableInterface $documentable, ?string $localeCode = null): Client
    {
        $factory = new Factory($this->getConfig($documentable, $localeCode));

        return $factory->buildClient();
    }

    public function getIndexBuilder(DocumentableInterface $documentable, ?string $localeCode = null): IndexBuilder
    {
        $factory = new Factory($this->getConfig($documentable, $localeCode));

        return $factory->buildIndexBuilder();
    }

    public function getIndexer(DocumentableInterface $documentable, ?string $localeCode = null): Indexer
    {
        $factory = new Factory($this->getConfig($documentable, $localeCode));

        return $factory->buildIndexer();
    }

    public function getIndexName(DocumentableInterface $documentable, ?string $locale): string
    {
        return $documentable->getIndexCode() . strtolower(null !== $locale ? '_' . $locale : '');
    }

    private function getConfig(DocumentableInterface $documentable, ?string $localeCode): array
    {
        $indexName = $this->getIndexName($documentable, $localeCode);
        $additionalConfig = [
            Factory::CONFIG_INDEX_CLASS_MAPPING => [
                $indexName => $documentable->getTargetClass(),
            ],
            Factory::CONFIG_MAPPINGS_PROVIDER => $documentable->getMappingProvider(),
            Factory::CONFIG_SERIALIZER => $this->serializer,
        ];

        return array_merge($this->config, $additionalConfig);
    }
}
