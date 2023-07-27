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

namespace MonsieurBiz\SyliusSearchPlugin\EventListener;

use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class ProductVariantEventListener
{
    private array $productIdsToReindex = [];

    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatchProductVariantReindexMessage(GenericEvent $event): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $event->getSubject();
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        if (null === $product = $variant->getProduct()) {
            return;
        }

        $productReindexFromIdsMessage = new ProductReindexFromIds();
        $productReindexFromIdsMessage->addProductId($product->getId());

        $this->messageBus->dispatch($productReindexFromIdsMessage);
    }

    public function saveProductIdToDispatchReindexMessage(GenericEvent $event): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $event->getSubject();
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        if (null === $product = $variant->getProduct()) {
            return;
        }

        $this->productIdsToReindex[] = $product->getId();
    }

    public function dispatchProductReindexMessage(GenericEvent $event): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $event->getSubject();
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        if (empty($this->productIdsToReindex)) {
            return;
        }

        $productReindexFromIdsMessage = new ProductReindexFromIds();
        foreach ($this->productIdsToReindex as $productIdToReindex) {
            $productReindexFromIdsMessage->addProductId($productIdToReindex);
        }

        $this->productIdsToReindex = [];
        $this->messageBus->dispatch($productReindexFromIdsMessage);
    }
}
