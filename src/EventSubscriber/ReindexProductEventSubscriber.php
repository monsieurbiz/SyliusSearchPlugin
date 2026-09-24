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

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use MonsieurBiz\SyliusSearchPlugin\Manager\AutomaticReindexManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;
use Throwable;

/**
 * This event subscriber only manages product taxons modifications.
 * For the other entities, we use the event listener and the event sylius (pre/post).
 */
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
class ReindexProductEventSubscriber implements LoggerAwareInterface, ResetInterface
{
    use LoggerAwareTrait;

    private MessageBusInterface $messageBus;

    private AutomaticReindexManagerInterface $automaticReindexManager;

    /** @var array<int, int> Existing identifiers preserve the queued message contract. */
    private array $taxonIds = [];

    public function __construct(MessageBusInterface $messageBus, AutomaticReindexManagerInterface $automaticReindexManager)
    {
        $this->messageBus = $messageBus;
        $this->automaticReindexManager = $automaticReindexManager;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush => 'onFlush',
            Events::postFlush => 'postFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $this->reset();
        if (!$this->automaticReindexManager->shouldBeAutomaticallyReindex()) {
            return;
        }

        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();
        $this->manageUnitOfWork($unitOfWork);
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) Only collect persisted taxon identifiers. */
    private function manageUnitOfWork(UnitOfWork $unitOfWork): void
    {
        $entities = array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates());
        foreach ($entities as $entity) {
            if ($entity instanceof ProductTaxonInterface && null !== ($taxon = $entity->getTaxon()) && null !== ($id = $taxon->getId())) {
                $this->taxonIds[$id] = $id;
            }
        }
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) Doctrine listener signature. */
    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $taxonIds = $this->taxonIds;
        $this->reset();

        try {
            foreach ($taxonIds as $taxonId) {
                $this->messageBus->dispatch(new ProductReindexFromTaxon($taxonId));
            }
        } catch (Throwable $exception) {
            $this->logger?->error('Taxon reindex dispatch failed after flush. Reconcile search with monsieurbiz:search:populate before resuming normal processing.', [
                'taxon_ids' => $taxonIds,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function reset(): void
    {
        $this->taxonIds = [];
    }
}
