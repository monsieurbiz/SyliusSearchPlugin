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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
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
        /** @phpstan-ignore-next-line */
        $repository = $this->entityManager->getRepository($sourceClass);
        $paginator = $this->getPaginator($repository);

        $page = 1;
        $paginator->setMaxPerPage(self::DEFAULT_MAX_PER_PAGE);
        do {
            $paginator->setCurrentPage($page);
            foreach ($paginator as $item) {
                yield $item;
            }
            $page = $paginator->hasNextPage() ? $paginator->getNextPage() : 1;
        } while ($paginator->hasNextPage());

        return null;
    }

    private function getPaginator(EntityRepository $repository): Pagerfanta
    {
        if ($repository instanceof RepositoryInterface && ($paginator = $repository->createPaginator()) instanceof Pagerfanta) {
            return $paginator;
        }

        return new Pagerfanta(new QueryAdapter($repository->createQueryBuilder('o'), false, false));
    }
}
