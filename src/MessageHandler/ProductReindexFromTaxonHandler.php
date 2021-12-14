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

namespace MonsieurBiz\SyliusSearchPlugin\MessageHandler;

use MonsieurBiz\SyliusSearchPlugin\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductReindexFromTaxonHandler implements MessageHandlerInterface
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

    public function __invoke(ProductReindexFromTaxon $message): void
    {
        $products = $this->productRepository->createQueryBuilder('o')
                ->innerJoin('o.productTaxons', 'productTaxon')
                ->andWhere('productTaxon.taxon = :taxonId')
                ->setParameter('taxonId', $message->getTaxonId())->getQuery()->getResult()
            ;

        $this->indexer->indexByDocuments(
            $this->documentableRegistry->get('search.documentable.monsieurbiz_product'),
            $products
        );
    }
}
