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
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class ProductEventListener
{
    private array $productIdsToDelete = [];

    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatchProductReindexMessage(GenericEvent $event): void
    {
        /** @var ProductInterface $product */
        $product = $event->getSubject();
        Assert::isInstanceOf($product, ProductInterface::class);

        $productReindexFromIdsMessage = new ProductReindexFromIds();
        $productReindexFromIdsMessage->addProductId($product->getId());

        $this->messageBus->dispatch($productReindexFromIdsMessage);
    }

    public function saveProductIdToDispatchReindexMessage(GenericEvent $event): void
    {
        /** @var ProductInterface $product */
        $product = $event->getSubject();
        Assert::isInstanceOf($product, ProductInterface::class);

        $this->productIdsToDelete[] = $product->getId();
    }

    public function dispatchDeleteProductReindexMessage(): void
    {
        if (empty($this->productIdsToDelete)) {
            return;
        }

        $productToDeleteFromIds = new ProductToDeleteFromIds();
        foreach ($this->productIdsToDelete as $productIdToDelete) {
            $productToDeleteFromIds->addProductId($productIdToDelete);
        }

        $this->productIdsToDelete = [];
        $this->messageBus->dispatch($productToDeleteFromIds);
    }
}
