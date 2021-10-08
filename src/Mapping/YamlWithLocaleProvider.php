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

use JoliCode\Elastically\Mapping\MappingProviderInterface;
use JoliCode\Elastically\Mapping\YamlProvider;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlWithLocaleProvider implements MappingProviderInterface
{
    private YamlProvider $decorated;
    private string $configurationDirectory;
    private Parser $parser;

    public function __construct(YamlProvider $decorated, string $configurationDirectory, ?Parser $parser = null)
    {
        $this->decorated = $decorated;
        $this->configurationDirectory = $configurationDirectory;
        $this->parser = $parser ?? new Parser();
    }

    public function provideMapping(string $indexName, array $context = []): ?array
    {
        $mapping = $this->decorated->provideMapping($context['index_code'] ?? $indexName, $context);

        $locale = $context['locale'] ?? null;
        if (null !== $mapping && null !== $locale) {
            $mapping = $this->appendLocaleAnalyzers($mapping, $locale);
        }

        return $mapping;
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
        return [
            current(explode('_', $locale)),
            $locale,
        ];
    }
}
