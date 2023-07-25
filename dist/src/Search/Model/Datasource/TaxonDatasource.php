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

namespace App\Search\Model\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\DatasourceInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Webmozart\Assert\Assert;

class TaxonDatasource implements DatasourceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getItems(string $sourceClass): iterable
    {
        $repository = $this->entityManager->getRepository($sourceClass);
        /** @var TaxonRepositoryInterface $repository */
        Assert::isInstanceOf($repository, TaxonRepositoryInterface::class);

        $queryBuilder = $repository->createQueryBuilder('o')
            ->andWhere('o.enabled = :enabled')
            ->setParameter('enabled', true)
        ;

        $paginator = new Pagerfanta(new QueryAdapter($queryBuilder, false, false));
        $paginator->setMaxPerPage(self::DEFAULT_MAX_PER_PAGE);
        $page = 1;
        do {
            $paginator->setCurrentPage($page);

            foreach ($paginator->getIterator() as $item) {
                yield $item;
            }
            $page = $paginator->hasNextPage() ? $paginator->getNextPage() : 1;
        } while ($paginator->hasNextPage());

        return null;
    }
}
