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

namespace MonsieurBiz\SyliusSearchPlugin\Mapping;

use ArrayObject;
use Elastica\Exception\InvalidException;
use JoliCode\Elastically\Mapping\MappingProviderInterface;
use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlWithLocaleProvider implements MappingProviderInterface
{
    private EventDispatcherInterface $eventDispatcher;


    private FileLocatorInterface $fileLocator;

    /**
     * @var array<string>
     */
    private iterable $configurationDirectories;

    private Parser $parser;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FileLocatorInterface $fileLocator,
        iterable $configurationDirectories = [],
        ?Parser $parser = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->fileLocator = $fileLocator;
        $this->configurationDirectories = $configurationDirectories;
        $this->parser = $parser ?? new Parser();
    }

    public function provideMapping(string $indexName, array $context = []): ?array
    {
        $mapping = [];
        $locale = $context['locale'] ?? null;
        foreach ($this->configurationDirectories as $configurationDirectory) {
            $configurationDirectory = $this->fileLocator->locate($configurationDirectory);
            if (!\is_string($configurationDirectory)) {
                continue;
            }
            $mapping = $this->appendMapping($configurationDirectory, $mapping, $indexName, $context);
            $mapping = $this->appendLocaleAnalyzers($configurationDirectory, $mapping, $locale);
        }

        $mappingProviderEvent = new MappingProviderEvent($context['index_code'] ?? $indexName, new ArrayObject($mapping));
        $this->eventDispatcher->dispatch(
            $mappingProviderEvent,
            MappingProviderEvent::EVENT_NAME
        );

        $mapping = (array) $mappingProviderEvent->getMapping();
        if (empty($mapping['mappings'] ?? [])) {
            throw new InvalidException(sprintf('Mapping no found for "%s" not found. Please check your configuration.', $indexName));
        }

        return $mapping;
    }

    private function appendMapping(string $configurationDirectory, array $mapping, string $indexName, array $context): array
    {
        try {
            $indexName = $context['index_code'] ?? $indexName;
            $fileName = $context['filename'] ?? ($indexName . '_mapping.yaml');
            $mappingFilePath = $configurationDirectory . \DIRECTORY_SEPARATOR . $fileName;

            $mapping = array_merge_recursive($mapping, $this->parser->parseFile($mappingFilePath));
        } catch (ParseException $exception) {
            // the mapping yaml file does not exist.
        }

        return $mapping;
    }

    private function appendLocaleAnalyzers(string $configurationDirectory, array $mapping, ?string $locale): array
    {
        if (null === $locale) {
            return $mapping;
        }

        $mapping = $this->appendAnalyzers($configurationDirectory . DIRECTORY_SEPARATOR . 'analyzers.yaml', $mapping);

        foreach ($this->getLocaleCode($locale) as $localeCode) {
            $mapping = $this->appendAnalyzers($configurationDirectory . \DIRECTORY_SEPARATOR . 'analyzers_' . $localeCode . '.yaml', $mapping);
        }

        return $mapping;
    }

    private function appendAnalyzers(string $analyzerFilePath, array $mapping): array
    {
        try {
            $analyzer = $this->parser->parseFile($analyzerFilePath) ?? [];
            $mapping['settings']['analysis'] = array_merge_recursive($mapping['settings']['analysis'] ?? [], $analyzer);
        } catch (ParseException $exception) {
            // the yaml file does not exist or does not exist.
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
