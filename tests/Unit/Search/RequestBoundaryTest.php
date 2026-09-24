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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Search;

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class RequestBoundaryTest extends SearchTestCase
{
    #[DataProvider('invalidInputs')]
    public function testMalformedHttpInputsAreRejectedRatherThanCausingServerErrors(array $input, string $method): void
    {
        $configuration = $this->configuration($input);
        $this->expectException(BadRequestHttpException::class);
        $configuration->{$method}();
    }

    public static function invalidInputs(): iterable
    {
        yield 'query array' => [['query' => ['shirt']], 'getQueryText'];
        yield 'sorting scalar' => [['sorting' => 'price'], 'getSorting'];
        yield 'sorting direction array' => [['sorting' => ['price' => ['asc']]], 'getSorting'];
        yield 'sorting unknown direction' => [['sorting' => ['name' => 'sideways']], 'getSorting'];
        yield 'sorting direction null' => [['sorting' => ['price' => null]], 'getSorting'];
        yield 'page zero' => [['page' => '0'], 'getPage'];
        yield 'page negative' => [['page' => '-2'], 'getPage'];
        yield 'page text' => [['page' => 'abc'], 'getPage'];
        yield 'page decimal' => [['page' => '1.5'], 'getPage'];
        yield 'page array' => [['page' => ['2']], 'getPage'];
        yield 'page overflow' => [['page' => str_repeat('9', 40)], 'getPage'];
        yield 'limit array' => [['limit' => ['9']], 'getLimit'];
        yield 'limit text' => [['limit' => 'all'], 'getLimit'];
        yield 'price nested bound' => [['price' => ['min' => ['1']]], 'getAppliedFilters'];
        yield 'price non numeric' => [['price' => ['max' => 'free']], 'getAppliedFilters'];
        yield 'price infinite' => [['price' => ['max' => '1e999']], 'getAppliedFilters'];
    }

    #[DataProvider('queries')]
    public function testQueriesRetainSupportedEncoding(string $query, string $expected): void
    {
        self::assertSame($expected, $this->configuration(['query' => $query])->getQueryText());
    }

    public static function queries(): iterable
    {
        yield ['  shirt  ', 'shirt'];
        yield ['caf%C3%A9%20bleu', 'café bleu'];
        yield ['a%2Fb', 'a/b'];
        yield ['C%2B%2B', 'C++'];
        yield ['', ''];
        yield ['0', '0'];
    }

    public function testZeroSelectionAndFreePriceMaximumAreNotDiscarded(): void
    {
        $configuration = $this->configuration(['price' => ['max' => '0'], 'taxons' => ['0']]);
        self::assertSame(['max' => '0'], $configuration->getAppliedFilters('price'));
        self::assertSame(['0'], $configuration->getAppliedFilters('taxons'));
    }

    public function testNormalisationIsIdempotentAndDoesNotDropOtherFilters(): void
    {
        $configuration = $this->configuration(['price' => ['min' => '30', 'max' => '10'], 'attributes' => ['material' => ['Cotton']]]);
        $first = $configuration->getAppliedFilters();
        self::assertSame(['min' => '10', 'max' => '30'], $first['price']);
        self::assertSame(['material' => ['Cotton']], $first['attributes']);
        self::assertSame($first, $configuration->getAppliedFilters());
    }

    public function testDefaultsAndCustomSortingKeysRemainAvailable(): void
    {
        $configuration = $this->configuration();
        self::assertSame('', $configuration->getQueryText());
        self::assertSame([], $configuration->getSorting());
        self::assertSame(1, $configuration->getPage());
        self::assertSame(9, $configuration->getLimit());
        self::assertSame(['short_description' => 'desc'], $this->configuration(['sorting' => ['short_description' => 'desc']])->getSorting());
        self::assertSame(18, $this->configuration(['limit' => '18'])->getLimit());
        self::assertSame(9, $this->configuration(['limit' => '999'])->getLimit());
    }

    #[DataProvider('requestLimits')]
    public function testDocumentLimitsDependOnTheRequestType(string $type, array $expected): void
    {
        self::assertSame($expected, $this->configuration([], $type)->getAvailableLimits());
    }

    public static function requestLimits(): iterable
    {
        yield [RequestInterface::SEARCH_TYPE, [9, 18, 27]];
        yield [RequestInterface::TAXON_TYPE, [9, 18, 27]];
        yield [RequestInterface::INSTANT_TYPE, [5]];
    }

    public function testChannelSettingsOverrideDocumentDefaults(): void
    {
        $settings = $this->createMock(SettingsInterface::class);
        $channel = $this->channelContext('WEB_FR')->getChannel();
        $context = $this->createStub(\Sylius\Component\Channel\Context\ChannelContextInterface::class);
        $context->method('getChannel')->willReturn($channel);
        $settings->expects(self::once())->method('getCurrentValue')->with($channel, null, 'limits__monsieurbiz_product')->willReturn(['search' => [12, 24]]);
        $configuration = new RequestConfiguration(Request::create('/'), RequestInterface::SEARCH_TYPE, $this->document(), $settings, $context);
        self::assertSame([12, 24], $configuration->getAvailableLimits());
    }

    public function testTaxonParametersPreserveIdentity(): void
    {
        $taxon = $this->taxon('shirts');
        $configuration = $this->configuration([], RequestInterface::TAXON_TYPE, $taxon);
        self::assertSame($taxon, $configuration->getTaxon());
        self::assertSame($taxon, $configuration->getParameters()->get('taxon'));
    }

    public function testMissingTaxonIsReported(): void
    {
        $this->expectException(\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException::class);
        $this->configuration()->getTaxon();
    }

    public function testWrongTaxonTypeIsReported(): void
    {
        $configuration = new RequestConfiguration(Request::create('/'), RequestInterface::TAXON_TYPE, $this->document(), $this->createStub(SettingsInterface::class), $this->channelContext(), new Parameters(['taxon' => 'shirts']));
        $this->expectException(\MonsieurBiz\SyliusSearchPlugin\Exception\ObjectNotInstanceOfClassException::class);
        $configuration->getTaxon();
    }
}
