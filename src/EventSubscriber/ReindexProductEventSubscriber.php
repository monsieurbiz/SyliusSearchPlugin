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

namespace MonsieurBiz\SyliusSearchPlugin\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ReindexProductEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ProductInterface[]
     */
    private array $productsToReindex = [];
    private array $productsToBeDelete = [];
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
            Events::postFlush => 'postFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $eventArgs->getEntityManager()->getEventManager()->removeEventListener(Events::onFlush, $this);
        $unitOfWork = $eventArgs->getEntityManager()->getUnitOfWork();

        $collections = array_merge($unitOfWork->getScheduledCollectionUpdates(), $unitOfWork->getScheduledCollectionDeletions());
        foreach ($collections as $collection) {
            if ($collection->getOwner() instanceof ProductInterface) {
                $this->productsToReindex[] = $collection->getOwner();
            }
        }

        $entities = array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates());
        $this->onFlushEntities($entities);
        $this->onFlushEntities($unitOfWork->getScheduledEntityDeletions(), 'deletions');

        if (0 !== \count($this->productsToBeDelete)) {
            $productToDeleteMessage = new ProductToDeleteFromIds();
            array_map(function(ProductInterface $product) use ($productToDeleteMessage): void {
                foreach ($this->productsToReindex as $key => $productsToReindex) {
                    if ($productsToReindex->getId() === $product->getId()) {
                        unset($this->productsToReindex[$key]);
                    }
                }
                $productToDeleteMessage->addProductId($product->getId());
            }, $this->productsToBeDelete);
            $this->messageBus->dispatch($productToDeleteMessage);
        }

        // in other event subscriber ...
        // todo reindex all data when: change/create/remove attribute/option, add/remove channel, add/remove locale
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $productReindexFormIdsMessage = new ProductReindexFromIds();

        foreach ($this->productsToReindex as $productsToReindex) {
            if (null === $productsToReindex->getId()) {
                continue;
            }
            $productReindexFormIdsMessage->addProductId($productsToReindex->getId());
        }
        $this->productsToReindex = [];

        if (0 !== \count($productReindexFormIdsMessage->getProductIds())) {
            $this->logger->info('Schedule reindex for: ' . implode(', ', $productReindexFormIdsMessage->getProductIds()), ['monsieurbiz.search']);
            $this->messageBus->dispatch($productReindexFormIdsMessage);
        }
    }

    private function onFlushEntities(array $entities, string $type = 'insertionsOrUpdate'): void
    {
        foreach ($entities as $entity) {
            if ($entity instanceof ProductInterface && 'deletions' === $type) {
                $this->productsToBeDelete[] = $entity;
                continue;
            }
            if ($entity instanceof ProductTaxonInterface) {
                $this->messageBus->dispatch(new ProductReindexFromTaxon($entity->getTaxon()->getId()));
                continue;
            }
            $product = $this->getProduct($entity);
            if (null !== $product) {
                $this->productsToReindex[] = $product;
            }
        }
    }

    private function getProduct($entity): ?\Sylius\Component\Product\Model\ProductInterface
    {
        $this->logger->info(\get_class($entity) . ': ' . implode(', ', class_implements($entity) ?? []), ['monsieurbiz.search']);

        switch (true) {
            case $entity instanceof ProductInterface:
                return $entity;
            case $entity instanceof ProductVariantInterface:
                return $entity->getProduct();
            case $entity instanceof ProductTaxonInterface:
                return $entity->getProduct();
            case $entity instanceof ProductTranslationInterface && $entity->getTranslatable() instanceof ProductInterface:
                return $entity->getTranslatable();
            case $entity instanceof ProductAttributeValueInterface:
                return $entity->getProduct();
            case $entity instanceof ProductImageInterface && $entity->getOwner() instanceof ProductInterface:
                return $entity->getOwner();
            case $entity instanceof ChannelPricingInterface && $entity->getProductVariant() instanceof ProductVariantInterface:
                return $entity->getProductVariant()->getProduct();
            case $entity instanceof ProductVariantTranslationInterface && $entity->getTranslatable() instanceof ProductVariantInterface:
                return $entity->getTranslatable()->getProduct();
        }

        return null;
    }
}
