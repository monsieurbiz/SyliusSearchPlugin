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
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use MonsieurBiz\SyliusSearchPlugin\Manager\AutomaticReindexManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This event subscriber only manages product taxons modifications.
 * For the other entities, we use the event listener and the event sylius (pre/post).
 */
class ReindexProductEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private MessageBusInterface $messageBus;

    private AutomaticReindexManagerInterface $automaticReindexManager;

    public function __construct(MessageBusInterface $messageBus, AutomaticReindexManagerInterface $automaticReindexManager)
    {
        $this->messageBus = $messageBus;
        $this->automaticReindexManager = $automaticReindexManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        if (!$this->automaticReindexManager->shouldBeAutomaticallyReindex()) {
            return;
        }

        $eventArgs->getEntityManager()->getEventManager()->removeEventListener(Events::onFlush, $this);
        $unitOfWork = $eventArgs->getEntityManager()->getUnitOfWork();
        $this->manageUnitOfWork($unitOfWork);
    }

    private function manageUnitOfWork(UnitOfWork $unitOfWork): void
    {
        $entities = array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates());
        foreach ($entities as $entity) {
            if ($entity instanceof ProductTaxonInterface && null !== $taxon = $entity->getTaxon()) {
                $this->messageBus->dispatch(new ProductReindexFromTaxon($taxon->getId()));
            }
        }
    }
}
