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

        $metadata->forMember('parent_taxon', function (TaxonInterface $taxon): ?TaxonDTO {
            return null !== $taxon->getParent()
                ? $this->autoMapper->map($taxon->getParent(), $this->configuration->getTargetClass('app_taxon'))
                : null;
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
}
