<?php

/*
 * This file is part of SyliusSearchPlugin.
 *
 * (c) Monsieur Biz
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\MessageHandler;

use MonsieurBiz\SyliusSearchPlugin\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductToDeleteFromIdsHandler implements MessageHandlerInterface
{
    private ProductRepositoryInterface $productRepository;
    private Indexer $indexer;
    private ServiceRegistryInterface $documentableRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Indexer $indexer,
        ServiceRegistryInterface $documentableRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->indexer = $indexer;
        $this->documentableRegistry = $documentableRegistry;
    }

    public function __invoke(ProductToDeleteFromIds $message)
    {
        $products = $this->productRepository->findBy(['id' => $message->getProductIds()]);

        $this->indexer->deleteByDocuments(
            $this->documentableRegistry->get('search.documentable.monsieurbiz_product'),
            $products
        );
    }
}