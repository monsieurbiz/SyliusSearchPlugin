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
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductReindexFromIdsHandler implements MessageHandlerInterface
{
    private ProductRepositoryInterface $productRepository;

    private IndexerInterface $indexer;

    private ServiceRegistryInterface $documentableRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        IndexerInterface $indexer,
        ServiceRegistryInterface $documentableRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->indexer = $indexer;
        $this->documentableRegistry = $documentableRegistry;
    }

    public function __invoke(ProductReindexFromIds $message): void
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $this->documentableRegistry->get('search.documentable.monsieurbiz_product');
        $products = $this->productRepository->findBy(['id' => $message->getProductIds()]);

        $this->indexer->indexByDocuments(
            $documentable,
            $products
        );
    }
}
