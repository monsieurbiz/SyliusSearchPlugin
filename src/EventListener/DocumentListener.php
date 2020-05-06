<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\EventListener;


use MonsieurBiz\SyliusSearchPlugin\Document\DocumentIndexer;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentableInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class DocumentListener
{
    /** @var DocumentIndexer */
    private $documentIndexer;

    /**
     * DocumentListener constructor.
     * @param DocumentIndexer $documentIndexer
     */
    public function __construct(DocumentIndexer $documentIndexer)
    {
        $this->documentIndexer = $documentIndexer;
    }

    /**
     * Save document to search index, update if exists
     *
     * @param GenericEvent $event
     * @throws \Exception
     */
    public function saveDocument(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, DocumentableInterface::class);

        $this->documentIndexer->indexOne($subject);
    }

    /**
     * Delete document in search index
     *
     * @param GenericEvent $event
     * @throws \Exception
     */
    public function deleteDocument(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, DocumentableInterface::class);

        $this->documentIndexer->removeOne($subject);
    }
}
