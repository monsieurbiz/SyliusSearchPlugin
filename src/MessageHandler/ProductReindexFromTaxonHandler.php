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

use Doctrine\ORM\EntityRepository;
use MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductReindexFromTaxonHandler implements MessageHandlerInterface
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

    public function __invoke(ProductReindexFromTaxon $message): void
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $this->documentableRegistry->get('search.documentable.monsieurbiz_product');
        if (!$this->productRepository instanceof EntityRepository) {
            return;
        }

        /** @var array $products */
        $products = $this->productRepository->createQueryBuilder('o')
                ->innerJoin('o.productTaxons', 'productTaxon')
                ->andWhere('productTaxon.taxon = :taxonId')
                ->setParameter('taxonId', $message->getTaxonId())->getQuery()->getResult()
        ;

        $this->indexer->indexByDocuments(
            $documentable,
            $products
        );
    }
}
