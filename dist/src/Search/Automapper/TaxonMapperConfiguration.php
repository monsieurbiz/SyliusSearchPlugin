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

namespace App\Search\Automapper;

use App\Search\Model\Taxon\TaxonDTO;
use DateTimeInterface;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Jane\Bundle\AutoMapperBundle\Configuration\MapperConfigurationInterface;
use Jane\Component\AutoMapper\AutoMapperInterface;
use Jane\Component\AutoMapper\MapperGeneratorMetadataInterface;
use Jane\Component\AutoMapper\MapperMetadata;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ConfigurationInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final class TaxonMapperConfiguration implements MapperConfigurationInterface
{
    private ConfigurationInterface $configuration;

    private AutoMapperInterface $autoMapper;

    public function __construct(
        ConfigurationInterface $configuration,
        AutoMapperInterface $autoMapper,
        private EntityManagerInterface $entityManager
    ) {
        $this->configuration = $configuration;
        $this->autoMapper = $autoMapper;
    }

    public function process(MapperGeneratorMetadataInterface $metadata): void
    {
        if (!$metadata instanceof MapperMetadata) {
            return;
        }

        $metadata->forMember('id', function (TaxonInterface $taxon): int {
            return $taxon->getId();
        });

        $metadata->forMember('code', function (TaxonInterface $taxon): ?string {
            return $taxon->getCode();
        });

        $metadata->forMember('enabled', function (TaxonInterface $taxon): bool {
            return $taxon->isEnabled();
        });

        $metadata->forMember('slug', function (TaxonInterface $taxon): ?string {
            return $taxon->getSlug();
        });

        $metadata->forMember('name', function (TaxonInterface $taxon): ?string {
            return $taxon->getName();
        });

        $metadata->forMember('description', function (TaxonInterface $taxon): ?string {
            return $taxon->getDescription();
        });

        $metadata->forMember('created_at', function (TaxonInterface $taxon): ?DateTimeInterface {
            return $taxon->getCreatedAt();
        });

        $metadata->forMember('position', function (TaxonInterface $taxon): ?int {
            return $taxon->getPosition();
        });

        $metadata->forMember('level', function (TaxonInterface $taxon): ?int {
            return $taxon->getLevel();
        });

        $metadata->forMember('left', function (TaxonInterface $taxon): ?int {
            return $taxon->getLeft();
        });

        $metadata->forMember('right', function (TaxonInterface $taxon): ?int {
            return $taxon->getRight();
        });

        /** @phpstan-ignore-next-line */
        $metadata->forMember('parent_taxon', function (TaxonInterface $taxon): ?TaxonDTO {
            return ($parent = $taxon->getParent()) ? $this->autoMapper->map(
                $this->getRealTaxonEntity($parent),
                $this->configuration->getTargetClass('app_taxon')
            ) : null;
        });
    }

    public function getSource(): string
    {
        return $this->configuration->getSourceClass('taxon');
    }

    public function getTarget(): string
    {
        return $this->configuration->getTargetClass('app_taxon');
    }

    private function getRealTaxonEntity(TaxonInterface $taxon): TaxonInterface
    {
        if ($taxon instanceof Proxy) {
            // Clear the entity manager to detach the proxy object
            $this->entityManager->clear($taxon::class);
            // Retrieve the original class name
            $entityClassName = ClassUtils::getRealClass($taxon::class);
            // Find the object in repository from the ID
            /** @var ?TaxonInterface $taxon */
            $taxon = $this->entityManager->find($entityClassName, $taxon->getId());
        }

        return $taxon;
    }
}
