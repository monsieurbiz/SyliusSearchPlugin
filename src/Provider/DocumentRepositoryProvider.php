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

namespace MonsieurBiz\SyliusSearchPlugin\Provider;

use Doctrine\ORM\EntityManagerInterface;

class DocumentRepositoryProvider
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $documentableClasses;

    /**
     * SearchQueryProvider constructor.
     *
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
