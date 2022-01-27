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

namespace MonsieurBiz\SyliusSearchPlugin\Mapping;

use ArrayObject;
use JoliCode\Elastically\Mapping\MappingProviderInterface;
use JoliCode\Elastically\Mapping\YamlProvider;
use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlWithLocaleProvider implements MappingProviderInterface
{
    private YamlProvider $decorated;
    private string $configurationDirectory;
    private Parser $parser;
    private ProductAttributeRepositoryInterface $attributeRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        YamlProvider $decorated,
        string $configurationDirectory,
        EventDispatcherInterface $eventDispatcher,
        ProductAttributeRepositoryInterface $attributeRepository,
        ?Parser $parser = null
    ) {
        $this->decorated = $decorated;
        $this->configurationDirectory = $configurationDirectory;
        $this->parser = $parser ?? new Parser();
        $this->attributeRepository = $attributeRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function provideMapping(string $indexName, array $context = []): ?array
    {
        $mapping = $this->decorated->provideMapping($context['index_code'] ?? $indexName, $context) ?? [];

        $locale = $context['locale'] ?? null;
        if (null !== $locale) {
            $mapping = $this->appendLocaleAnalyzers($mapping, $locale);
        }

        $mappingProviderEvent = new MappingProviderEvent($context['index_code'] ?? $indexName, new ArrayObject($mapping));
        $this->eventDispatcher->dispatch(
            $mappingProviderEvent,
            MappingProviderEvent::EVENT_NAME
        );

        return (array) $mappingProviderEvent->getMapping();
    }

    private function appendLocaleAnalyzers(array $mapping, string $locale): array
    {
        foreach ($this->getLocaleCode($locale) as $localeCode) {
            $analyzerFilePath = $this->configurationDirectory . \DIRECTORY_SEPARATOR . 'analyzers_' . $localeCode . '.yaml';
            try {
                $analyzer = $this->parser->parseFile($analyzerFilePath) ?? [];
                $mapping['settings']['analysis'] = array_merge_recursive($mapping['settings']['analysis'] ?? [], $analyzer);
            } catch (ParseException $exception) {
                // the yaml file does not exist or does not exist.
            }
        }

        return $mapping;
    }

    private function getLocaleCode(string $locale): array
    {
        return array_unique([
            current(explode('_', $locale)),
            $locale,
        ]);
    }
}
