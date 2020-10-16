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

namespace MonsieurBiz\SyliusSearchPlugin\EventListener;

use MonsieurBiz\SyliusSearchPlugin\Model\Document\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class DocumentListener
{
    /** @var Indexer */
    private $documentIndexer;

    /**
     * DocumentListener constructor.
     *
     * @param Indexer $documentIndexer
     */
    public function __construct(Indexer $documentIndexer)
    {
        $this->documentIndexer = $documentIndexer;
    }

    /**
     * Save document to search index, update if exists.
     *
     * @param GenericEvent $event
     *
     * @throws \Exception
     */
    public function saveDocument(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, DocumentableInterface::class);

        $this->documentIndexer->indexOne($subject);
    }

    /**
     * Delete document in search index.
     *
     * @param GenericEvent $event
     *
     * @throws \Exception
     */
    public function deleteDocument(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, DocumentableInterface::class);

        $this->documentIndexer->removeOne($subject);
    }
}
