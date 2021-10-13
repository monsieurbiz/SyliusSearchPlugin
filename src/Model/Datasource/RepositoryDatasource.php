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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class RepositoryDatasource implements DatasourceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getItems(string $sourceClass): iterable
    {
        $repository = $this->entityManager->getRepository($sourceClass);
        if ($repository instanceof RepositoryInterface) {
            /** @var Pagerfanta $paginator */
            $paginator = $repository->createPaginator();
            $page = 1;
            while ($paginator->hasNextPage()) {
                $paginator->setCurrentPage($page);
                foreach ($paginator as $item) {
                    yield $item;
                }
                if ($paginator->hasNextPage()) {
                    $page = $paginator->getNextPage();
                }
            }

            return null;
        }

        return $repository->createQueryBuilder('o')->getQuery()->toIterable();
    }
}
