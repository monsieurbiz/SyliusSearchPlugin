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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Mapping;

use ArrayObject;
use Elastica\Exception\InvalidException;
use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use MonsieurBiz\SyliusSearchPlugin\Mapping\YamlWithLocaleProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Exception\ParseException;

final class YamlMappingTest extends TestCase
{
    #[DataProvider('locales')]
    public function testAnalyzerOrderAndLocaleDeduplication(?string $locale, array $filters): void
    {
        $provider = new YamlWithLocaleProvider(new EventDispatcher(), new FileLocator(), [$this->directory('base')]);
        $mapping = $provider->provideMapping('product', ['locale' => $locale]);
        self::assertFalse($mapping['mappings']['dynamic']);
        self::assertSame('text', $mapping['mappings']['properties']['name']['type']);
        self::assertSame($filters, $mapping['settings']['analysis']['analyzer']['search']['filter']);
    }

    public static function locales(): iterable
    {
        yield [null, ['lowercase']];
        yield ['fr', ['lowercase', 'french_stemmer']];
        yield ['fr_FR', ['lowercase', 'french_stemmer', 'regional_filter']];
        yield ['en_US', ['lowercase']];
    }

    public function testLaterDirectoriesOverrideScalarConfigurationWithoutDestroyingOtherFields(): void
    {
        $provider = new YamlWithLocaleProvider(new EventDispatcher(), new FileLocator(), [$this->directory('base'), $this->directory('override')]);
        $mapping = $provider->provideMapping('product', ['locale' => 'fr']);
        self::assertSame('keyword', $mapping['mappings']['properties']['name']['type']);
        self::assertSame('text', $mapping['mappings']['properties']['custom']['type']);
        self::assertSame(2, $mapping['settings']['number_of_shards']);
        self::assertSame('icu_tokenizer', $mapping['settings']['analysis']['analyzer']['search']['tokenizer']);
        self::assertSame(['lowercase', 'french_stemmer'], $mapping['settings']['analysis']['analyzer']['search']['filter']);
    }

    public function testIndexCodeAndFilenameOverridesAreIndependentOfPhysicalIndexName(): void
    {
        $provider = new YamlWithLocaleProvider(new EventDispatcher(), new FileLocator(), [$this->directory('base')]);
        self::assertSame('text', $provider->provideMapping('physical_index_123', ['index_code' => 'product'])['mappings']['properties']['name']['type']);
        self::assertSame('keyword', $provider->provideMapping('physical', ['filename' => 'alternate.yaml'])['mappings']['properties']['alternate']['type']);
    }

    public function testEventCanSupplyMappingAndReceivesUnmodifiedContext(): void
    {
        $context = ['index_code' => 'custom', 'locale' => 'fr_FR', 'tenant' => 'A'];
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(MappingProviderEvent::EVENT_NAME, static function (MappingProviderEvent $event) use ($context): void {
            self::assertSame('custom', $event->getIndexCode());
            self::assertSame($context, $event->getContext());
            $event->getMapping()['mappings'] = ['properties' => ['code' => ['type' => 'keyword']]];
        });
        $mapping = (new YamlWithLocaleProvider($dispatcher, new FileLocator(), []))->provideMapping('physical', $context);
        self::assertSame(['code' => ['type' => 'keyword']], $mapping['mappings']['properties']);
    }

    public function testMissingMappingIsRejectedAfterListenersHaveRun(): void
    {
        $this->expectException(InvalidException::class);
        (new YamlWithLocaleProvider(new EventDispatcher(), new FileLocator(), [$this->directory('base')]))->provideMapping('absent');
    }

    public function testMalformedExistingYamlIsNotSilentlyIgnored(): void
    {
        $this->expectException(ParseException::class);
        (new YamlWithLocaleProvider(new EventDispatcher(), new FileLocator(), [$this->directory('base'), $this->directory('malformed')]))->provideMapping('product');
    }

    public function testLocatorArrayIsNotMistakenForADirectory(): void
    {
        $locator = $this->createStub(FileLocatorInterface::class);
        $locator->method('locate')->willReturn([$this->directory('base')]);
        $this->expectException(InvalidException::class);
        (new YamlWithLocaleProvider(new EventDispatcher(), $locator, ['alias']))->provideMapping('product');
    }

    public function testMappingEventRetainsMutableObjectIdentity(): void
    {
        $mapping = new ArrayObject(['mappings' => []]);
        $event = new MappingProviderEvent('product', $mapping, ['locale' => 'fr']);
        self::assertSame($mapping, $event->getMapping());
        $mapping['mappings'] = ['dynamic' => false];
        self::assertSame(['dynamic' => false], $event->getMapping()['mappings']);
        self::assertNull((new MappingProviderEvent('product', null))->getMapping());
    }

    private function directory(string $name): string
    {
        return \dirname(__DIR__, 2) . '/Fixtures/Mapping/' . $name;
    }
}
