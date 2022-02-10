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

namespace MonsieurBiz\SyliusSearchPlugin\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
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
use Sylius\Component\Product\Model\ProductInterface as ModelProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ReindexProductEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ModelProductInterface[]
     */
    private array $productsToReindex = [];

    private array $productsToBeDelete = [];

    private MessageBusInterface $messageBus;

    private bool $dispatched = false;

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
        $this->manageUnitOfWork($unitOfWork);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        $this->manageUnitOfWork($unitOfWork);

        $productReindexFromIdsMessage = new ProductReindexFromIds();

        foreach ($this->productsToReindex as $productsToReindex) {
            if (null === $productsToReindex->getId()) {
                continue;
            }
            $productReindexFromIdsMessage->addProductId($productsToReindex->getId());
        }
        $this->productsToReindex = [];

        if (0 !== \count($productReindexFromIdsMessage->getProductIds()) && false === $this->dispatched) {
            $this->dispatched = true; // Needed to set before dispatch to avoid infinite calls by message flush containing product
            $this->messageBus->dispatch($productReindexFromIdsMessage);
        }
    }

    private function onFlushEntities(array $entities, string $type = 'insertionsOrUpdate'): void
    {
        foreach ($entities as $entity) {
            if ($entity instanceof ProductInterface && 'deletions' === $type) {
                $this->productsToBeDelete[$entity->getId()] = $entity;

                continue;
            }
            if ($entity instanceof ProductTaxonInterface && null !== $entity->getTaxon()) {
                $this->messageBus->dispatch(new ProductReindexFromTaxon($entity->getTaxon()->getId()));

                continue;
            }
            $product = $this->getProduct($entity);
            if (null !== $product) {
                $this->productsToReindex[$product->getId()] = $product;
            }
        }
    }

    private function getProduct(object $entity): ?ModelProductInterface
    {
        switch (true) {
            case $entity instanceof ProductInterface:
                return $entity;
            case $entity instanceof ProductVariantInterface:
                return $entity->getProduct();
            case $entity instanceof ProductTaxonInterface:
                return $entity->getProduct();
            case $entity instanceof ProductTranslationInterface && $entity->getTranslatable() instanceof ProductInterface:
                /** @var ProductInterface $product */
                $product = $entity->getTranslatable();

                return $product;
            case $entity instanceof ProductAttributeValueInterface:
                return $entity->getProduct();
            case $entity instanceof ProductImageInterface && $entity->getOwner() instanceof ProductInterface:
                /** @var ProductInterface $product */
                $product = $entity->getOwner();

                return $product;
            case $entity instanceof ChannelPricingInterface && $entity->getProductVariant() instanceof ProductVariantInterface:
                /** @var ProductVariantInterface $productVariant */
                $productVariant = $entity->getProductVariant();

                return $productVariant->getProduct();
            case $entity instanceof ProductVariantTranslationInterface && $entity->getTranslatable() instanceof ProductVariantInterface:
                /** @var ProductVariantInterface $productVariant */
                $productVariant = $entity->getTranslatable();

                return $productVariant->getProduct();
        }

        return null;
    }

    private function manageUnitOfWork(UnitOfWork $unitOfWork): void
    {
        $collections = array_merge($unitOfWork->getScheduledCollectionUpdates(), $unitOfWork->getScheduledCollectionDeletions());
        foreach ($collections as $collection) {
            if (method_exists($collection, 'getOwner') && $collection->getOwner() instanceof ProductInterface) {
                $product = $collection->getOwner();
                $this->productsToReindex[$product->getId()] = $product;
            }
        }

        $entities = array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates());
        $this->onFlushEntities($entities);
        $this->onFlushEntities($unitOfWork->getScheduledEntityDeletions(), 'deletions');

        if (0 !== \count($this->productsToBeDelete)) {
            $productToDeleteMessage = new ProductToDeleteFromIds();
            array_map(function (ProductInterface $product) use ($productToDeleteMessage): void {
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
}
