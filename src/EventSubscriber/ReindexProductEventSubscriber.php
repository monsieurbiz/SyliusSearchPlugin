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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
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

    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $eventArgs->getEntityManager()->getEventManager()->removeEventListener(Events::onFlush, $this);
        $productReindexFormIdsMessage = new ProductReindexFromIds();
        $unitOfWork = $eventArgs->getEntityManager()->getUnitOfWork();

        $collections = array_merge($unitOfWork->getScheduledCollectionUpdates(), $unitOfWork->getScheduledCollectionDeletions());
        foreach ($collections as $collection) {
            if ($collection->getOwner() instanceof ProductInterface) {
                $productReindexFormIdsMessage->addProductId($collection->getOwner()->getId());
            }
        }

        $entities = array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates(), $unitOfWork->getScheduledEntityDeletions());
        foreach ($entities as $entity) {
            if ($entity instanceof ProductTaxonInterface) {
                $this->messageBus->dispatch(new ProductReindexFromTaxon($entity->getTaxon()->getId()));
                continue;
            }
            $product = $this->getProduct($eventArgs->getEntityManager(), $entity);
            if (null !== $product) {
                $productReindexFormIdsMessage->addProductId($product->getId());
            }
        }

        if (0 !== count($productReindexFormIdsMessage->getProductIds())) {
            $this->logger->info('Schedule reindex for:'. implode(', ', $productReindexFormIdsMessage->getProductIds()), ['monsieurbiz.search']);
            $this->messageBus->dispatch($productReindexFormIdsMessage);
        }

        // todo delete : product

        // in other event subscriber ...
        // todo reindex all data when: change/create/remove attribute/option, add/remove channel, add/remove locale
    }

    private function getProduct(EntityManagerInterface $entityManager, $entity): ?\Sylius\Component\Product\Model\ProductInterface
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
