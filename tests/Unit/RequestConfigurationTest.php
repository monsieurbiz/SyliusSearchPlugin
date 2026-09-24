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

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class RequestConfigurationTest extends TestCase
{
    #[DataProvider('ranges')]
    public function testOpenAndReversedPriceBounds(array $input, array $expected): void
    {
        $configuration = new RequestConfiguration(
            Request::create('/search/shirt', 'GET', ['price' => $input]),
            RequestInterface::SEARCH_TYPE,
            $this->createMock(DocumentableInterface::class),
            $this->createMock(SettingsInterface::class),
            $this->createMock(ChannelContextInterface::class),
        );

        self::assertSame($expected, $configuration->getAppliedFilters('price'));
    }

    public static function ranges(): iterable
    {
        yield 'minimum only' => [['min' => '20', 'max' => ''], ['min' => '20']];
        yield 'maximum only' => [['min' => '', 'max' => '20'], ['max' => '20']];
        yield 'reversed' => [['min' => '30', 'max' => '20'], ['min' => '20', 'max' => '30']];
        yield 'negative minimum' => [['min' => '-1', 'max' => '20'], ['max' => '20']];
        yield 'empty range' => [['min' => '', 'max' => ''], []];
        yield 'decimal range' => [['min' => '10.25', 'max' => '20.50'], ['min' => '10.25', 'max' => '20.50']];
    }
}
