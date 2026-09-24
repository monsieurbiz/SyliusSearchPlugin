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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Support;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\Documentable;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\Taxon;
use Symfony\Component\HttpFoundation\Request;

abstract class SearchTestCase extends TestCase
{
    protected function configuration(array $query = [], string $type = RequestInterface::SEARCH_TYPE, ?Taxon $taxon = null): RequestConfiguration
    {
        return new RequestConfiguration(
            Request::create('/search', 'GET', $query),
            $type,
            $this->document(),
            $this->createStub(SettingsInterface::class),
            $this->channelContext(),
            new Parameters(null === $taxon ? [] : ['taxon' => $taxon]),
        );
    }

    protected function document(string $code = 'monsieurbiz_product'): Documentable
    {
        return new Documentable($code, ProductInterface::class, ProductDTO::class, ['item' => 'item.html.twig', 'instant' => 'instant.html.twig'], [
            'search' => [9, 18, 27], 'taxon' => [9, 18, 27], 'instant_search' => [5],
        ]);
    }

    protected function channelContext(string $code = 'WEB_US'): ChannelContextInterface
    {
        $channel = new Channel();
        $channel->setCode($code);
        $context = $this->createStub(ChannelContextInterface::class);
        $context->method('getChannel')->willReturn($channel);

        return $context;
    }

    protected function taxon(string $code, ?Taxon $parent = null): Taxon
    {
        $taxon = new Taxon();
        $taxon->setCode($code);
        $taxon->setCurrentLocale('en_US');
        $taxon->setFallbackLocale('en_US');
        $taxon->setName(ucfirst($code));
        $taxon->setParent($parent);
        $taxon->setLevel(null === $parent ? 0 : $parent->getLevel() + 1);

        return $taxon;
    }
}
