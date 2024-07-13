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

use MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductToDeleteFromIdsHandler implements MessageHandlerInterface
{
    private IndexerInterface $indexer;

    private ServiceRegistryInterface $documentableRegistry;

    public function __construct(
        IndexerInterface $indexer,
        ServiceRegistryInterface $documentableRegistry
    ) {
        $this->indexer = $indexer;
        $this->documentableRegistry = $documentableRegistry;
    }

    public function __invoke(ProductToDeleteFromIds $message): void
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $this->documentableRegistry->get('search.documentable.monsieurbiz_product');

        $this->indexer->deleteByDocumentIds(
            $documentable,
            $message->getProductIds()
        );
    }
}
