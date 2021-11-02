<?php

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
        return $this->productOptionRepository->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('o.searchable = true')
            ->orWhere('o.filterable = true')
            ->getQuery()
            ->getResult()
            ;
    }
}
