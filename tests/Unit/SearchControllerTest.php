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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit;

use MonsieurBiz\SyliusSearchPlugin\Controller\SearchController;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\ResponseInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Currency\Model\Currency;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SearchControllerTest extends TestCase
{
    public function testPriceFiltersExplicitlyUseBaseCurrencyWhenShopperSelectsAnotherCurrency(): void
    {
        $baseCurrency = new Currency();
        $baseCurrency->setCode('USD');
        $channel = new Channel();
        $channel->setBaseCurrency($baseCurrency);
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);
        $currencyContext = $this->createMock(CurrencyContextInterface::class);
        $currencyContext->method('getCurrencyCode')->willReturn('EUR');
        $locale = $this->createMock(LocaleContextInterface::class);
        $locale->method('getLocaleCode')->willReturn('en_US');
        $document = $this->createMock(DocumentableInterface::class);
        $registry = $this->createMock(ServiceRegistryInterface::class);
        $registry->method('all')->willReturn([$document]);
        $settings = $this->createMock(SettingsInterface::class);
        $settings->method('getCurrentValue')->willReturn(true);
        $result = $this->createMock(ResponseInterface::class);
        $result->method('getDocumentable')->willReturn($document);
        $search = $this->createMock(Search::class);
        $search->method('search')->willReturn($result);
        $controller = $this->getMockBuilder(SearchController::class)
            ->setConstructorArgs([$search, $currencyContext, $locale, $channelContext, $settings, $registry, $this->createMock(ParametersParserInterface::class)])
            ->onlyMethods(['render'])
            ->getMock()
        ;
        $controller->expects(self::once())->method('render')->with(
            '@MonsieurBizSyliusSearchPlugin/Search/result.html.twig',
            self::callback(static fn (array $parameters): bool => 'USD ($)' === $parameters['currencySymbol']),
        )->willReturn(new Response());

        self::assertSame(200, $controller->searchAction(Request::create('/search/shirt'), 'shirt')->getStatusCode());
    }
}
