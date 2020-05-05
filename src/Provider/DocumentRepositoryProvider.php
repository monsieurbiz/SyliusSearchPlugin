<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Provider;

use Doctrine\ORM\EntityManagerInterface;

class DocumentRepositoryProvider
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $documentableClasses;

    /**
     * SearchRequestProvider constructor.
     * @param EntityManagerInterface $entityManager
     * @param array $documentableClasses
     */
    public function __construct(EntityManagerInterface $entityManager, array $documentableClasses)
    {
        $this->entityManager = $entityManager;
        $this->documentableClasses = $documentableClasses;
    }

    public function getRepositories()
    {
        $repositories = [];
        foreach ($this->documentableClasses as $class) {
            $repositories[] = $this->entityManager->getRepository($class);
        }
        return $repositories;
    }
}
