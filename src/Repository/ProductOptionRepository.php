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

namespace MonsieurBiz\SyliusSearchPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ProductOptionRepository implements ProductOptionRepositoryInterface
{
    private EntityRepository $productOptionRepository;

    public function __construct(EntityRepository $productOptionRepository)
    {
        $this->productOptionRepository = $productOptionRepository;
    }

    public function findIsSearchableOrFilterable(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->productOptionRepository->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('o.searchable = true')
            ->orWhere('o.filterable = true')
            ->getQuery()
            ->getResult()
        ;
    }
}
