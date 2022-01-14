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

namespace MonsieurBiz\SyliusSearchPlugin\Resolver;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;

class CheapestProductVariantResolver implements ProductVariantResolverInterface
{
    private ChannelContextInterface $channelContext;

    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    public function getVariant(ProductInterface $subject): ?ProductVariantInterface
    {
        if ($subject->getEnabledVariants()->isEmpty()) {
            return null;
        }

        $cheapestVariant = null;
        $cheapestPrice = null;
        $variants = $subject->getEnabledVariants();
        foreach ($variants as $variant) {
            if (null === ($channelPrice = $variant->getChannelPricingForChannel($this->channelContext->getChannel()))) {
                continue;
            }
            if (null === $cheapestPrice || $channelPrice->getPrice() < $cheapestPrice) {
                $cheapestPrice = $channelPrice->getPrice();
                $cheapestVariant = $variant;
            }
        }

        return $cheapestVariant;
    }
}
