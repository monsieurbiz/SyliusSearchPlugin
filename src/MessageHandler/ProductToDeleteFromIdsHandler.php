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

namespace MonsieurBiz\SyliusSearchPlugin\MessageHandler;

use Exception;
use MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductToDeleteFromIdsHandler implements MessageHandlerInterface
{
    private IndexerInterface $indexer;

    private ServiceRegistryInterface $documentableRegistry;

    private LoggerInterface $logger;

    public function __construct(
        IndexerInterface $indexer,
        ServiceRegistryInterface $documentableRegistry,
        LoggerInterface $logger
    ) {
        $this->indexer = $indexer;
        $this->documentableRegistry = $documentableRegistry;
        $this->logger = $logger;
    }

    public function __invoke(ProductToDeleteFromIds $message): void
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $this->documentableRegistry->get('search.documentable.monsieurbiz_product');

        try {
            $this->indexer->deleteByDocumentIds(
                $documentable,
                $message->getProductIds()
            );
        } catch (Exception $e) {
            $this->logger->error('An error occurred while deleting products from search index', [
                'exception' => $e,
            ]);
        }
    }
}
