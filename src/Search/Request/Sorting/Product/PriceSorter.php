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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product;

use Elastica\Query;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterBuilderTrait;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final class PriceSorter implements SorterInterface
{
    use SorterBuilderTrait;

    private ChannelContextInterface $channelContext;

    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    public function apply(Query $query, RequestConfiguration $requestConfiguration): void
    {
        $sorting = $requestConfiguration->getSorting();
        if (!\array_key_exists('price', $sorting)) {
            return;
        }

        $query->addSort(
            $this->buildSort(
                'prices.price',
                $sorting['price'],
                'prices',
                'prices.channel_code',
                $this->channelContext->getChannel()->getCode()
            )
        );
    }
}
