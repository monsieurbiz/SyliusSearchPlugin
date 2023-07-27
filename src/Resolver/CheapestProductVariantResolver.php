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

namespace MonsieurBiz\SyliusSearchPlugin\Resolver;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface as ModelProductVariantInterface;
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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getVariant(ProductInterface $subject): ?ProductVariantInterface
    {
        $channel = $this->channelContext->getChannel();
        if ($subject->getEnabledVariants()->isEmpty() || !$channel instanceof ChannelInterface) {
            return null;
        }

        $cheapestVariant = null;
        $cheapestPrice = null;
        $variants = $subject->getEnabledVariants();
        foreach ($variants as $variant) {
            if (!$variant instanceof ModelProductVariantInterface) {
                continue;
            }
            if (null === ($channelPrice = $variant->getChannelPricingForChannel($channel))) {
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
