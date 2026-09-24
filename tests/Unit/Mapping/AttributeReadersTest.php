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

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader as Reader;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Stringable;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use UnexpectedValueException;

final class AttributeReadersTest extends TestCase
{
    #[DataProvider('scalarValues')]
    public function testScalarReadersPreserveTheirTextualValues(mixed $input, string $expected): void
    {
        $value = $this->value($input);
        foreach ([new Reader\TextReader(), new Reader\TextareaReader(), new Reader\IntegerReader(), new Reader\PercentReader(), new Reader\CheckboxReader()] as $reader) {
            self::assertSame($expected, $reader->getValue($value));
        }
    }

    public static function scalarValues(): iterable
    {
        yield ['Cotton', 'Cotton'];
        yield [0, '0'];
        yield [42, '42'];
        yield [1.5, '1.5'];
        yield [true, '1'];
        yield [false, ''];
        yield [null, ''];
    }

    public function testStringableValueIsAcceptedButAnUnstructuredObjectIsRejected(): void
    {
        $object = new class() implements Stringable {
            public function __toString(): string
            {
                return 'Cotton';
            }
        };
        self::assertSame('Cotton', (new Reader\TextReader())->getValue($this->value($object)));
        $this->expectException(UnexpectedValueException::class);
        (new Reader\TextReader())->getValue($this->value(new stdClass()));
    }

    public function testScalarReaderDoesNotSilentlyCastAnArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new Reader\TextReader())->getValue($this->value(['Cotton']));
    }

    #[DataProvider('dateValues')]
    public function testDateReadersUseTheirOwnFormatAndSupportImmutableDates(mixed $input, mixed $date, mixed $dateTime): void
    {
        self::assertSame($date, (new Reader\DateReader())->getValue($this->value($input)));
        self::assertSame($dateTime, (new Reader\DateTimeReader())->getValue($this->value($input)));
    }

    public static function dateValues(): iterable
    {
        yield [new DateTime('2024-02-29 15:16:17'), '2024-02-29', '2024-02-29 15:16:17'];
        yield [new DateTimeImmutable('2024-02-29 15:16:17'), '2024-02-29', '2024-02-29 15:16:17'];
        yield ['already formatted', 'already formatted', 'already formatted'];
        yield [['custom'], ['custom'], ['custom']];
    }

    public function testMissingAttributeReturnsEmptyDateRatherThanFormattingIt(): void
    {
        $value = $this->createStub(ProductAttributeValueInterface::class);
        $value->method('getAttribute')->willReturn(null);
        self::assertSame('', (new Reader\DateTimeReader())->getValue($value));
        self::assertSame('', $this->selectReader()->getValue($value));
    }

    public function testInvalidDateValueIsRejected(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new Reader\DateTimeReader())->getValue($this->value(123));
    }

    public function testSelectPrefersAttributeLocaleThenFallsBackAndPreservesOrder(): void
    {
        $value = $this->value(['cotton', 'wool', 'cotton', 'missing', ['malformed'], 0]);
        $value->getAttribute()->setCurrentLocale('fr_FR');
        $value->getAttribute()->setConfiguration(['choices' => [
            'cotton' => ['en_US' => 'Cotton', 'fr_FR' => 'Coton'],
            'wool' => ['en_US' => 'Wool'], 0 => ['en_US' => 'Zero'],
        ]]);
        self::assertSame(['Coton', 'Wool', 'Coton', 'Zero'], $this->selectReader()->getValue($value));
    }

    public function testScalarSelectAndMalformedChoiceLabels(): void
    {
        $value = $this->value('cotton');
        $value->getAttribute()->setConfiguration(['choices' => ['cotton' => ['en_US' => 'Cotton']]]);
        self::assertSame(['Cotton'], $this->selectReader()->getValue($value));
        $value->getAttribute()->setConfiguration(['choices' => ['cotton' => 'not a translations map']]);
        self::assertSame([], $this->selectReader()->getValue($value));
        $value->getAttribute()->setConfiguration(['choices' => ['cotton' => ['en_US' => 42]]]);
        self::assertSame([], $this->selectReader()->getValue($value));
    }

    public function testMalformedSelectConfigurationFailsExplicitly(): void
    {
        $value = $this->value('cotton');
        $value->getAttribute()->setConfiguration(['choices' => 'bad']);
        $this->expectException(UnexpectedValueException::class);
        $this->selectReader()->getValue($value);
    }

    public function testReaderCodesAreStableRegistryKeys(): void
    {
        foreach (['text' => Reader\TextReader::class, 'textarea' => Reader\TextareaReader::class, 'integer' => Reader\IntegerReader::class, 'percent' => Reader\PercentReader::class, 'checkbox' => Reader\CheckboxReader::class, 'date' => Reader\DateReader::class, 'datetime' => Reader\DateTimeReader::class, 'select' => Reader\SelectReader::class] as $code => $class) {
            self::assertSame($code, $class::getReaderCode());
        }
    }

    public function testResolverUsesTraversableRegistryAndPreservesReaderResult(): void
    {
        $resolver = new ProductAttributeValueResolver(new ArrayIterator(['text' => new Reader\TextReader()]));
        self::assertSame('Cotton', $resolver->getProductAttributeValue($this->value('Cotton')));
    }

    public function testResolverLogsUnknownTypesAndDoesNotGuessAReader(): void
    {
        $resolver = new ProductAttributeValueResolver(['integer' => new Reader\IntegerReader()]);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('alert')->with(self::stringContains('text'));
        $resolver->setLogger($logger);
        self::assertNull($resolver->getProductAttributeValue($this->value('Cotton')));
    }

    public function testMissingTypeDoesNotInvokeReader(): void
    {
        $reader = $this->createMock(Reader\ReaderInterface::class);
        $reader->expects(self::never())->method('getValue');
        $value = $this->createStub(ProductAttributeValueInterface::class);
        $value->method('getType')->willReturn(null);
        self::assertNull((new ProductAttributeValueResolver(['text' => $reader]))->getProductAttributeValue($value));
    }

    public function testEmptyRegistryIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        new ProductAttributeValueResolver([]);
    }

    private function value(mixed $data): ProductAttributeValueInterface
    {
        $attribute = new ProductAttribute();
        $attribute->setCurrentLocale('en_US');
        $attribute->setFallbackLocale('en_US');
        $value = $this->createStub(ProductAttributeValueInterface::class);
        $value->method('getValue')->willReturn($data);
        $value->method('getAttribute')->willReturn($attribute);
        $value->method('getLocaleCode')->willReturn('en_US');
        $value->method('getType')->willReturn('text');

        return $value;
    }

    private function selectReader(): Reader\SelectReader
    {
        $locale = $this->createStub(TranslationLocaleProviderInterface::class);
        $locale->method('getDefaultLocaleCode')->willReturn('en_US');

        return new Reader\SelectReader($locale);
    }
}
