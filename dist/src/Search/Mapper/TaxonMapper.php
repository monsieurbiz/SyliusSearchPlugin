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

namespace App\Search\Mapper;

use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ConfigurationInterface;
use MonsieurBiz\SyliusSearchPlugin\Mapper\DocumentMappingInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class TaxonMapper implements DocumentMappingInterface
{
    public function __construct(private ConfigurationInterface $configuration, private PropertyAccessorInterface $propertyAccessor)
    {
    }

    public function supports(object $source, string $targetClass): bool
    {
        return $source instanceof TaxonInterface && is_a($source, $this->configuration->getSourceClass('taxon')) && $targetClass === $this->configuration->getTargetClass('app_taxon');
    }

    public function map(object $source, string $targetClass): object
    {
        if (!$source instanceof TaxonInterface) {
            throw new InvalidArgumentException('Expected a taxon.');
        }
        $values = [
            'id' => $source->getId(),
            'code' => $source->getCode(),
            'enabled' => $source->isEnabled(),
            'slug' => $source->getSlug(),
            'name' => $source->getName(),
            'description' => $source->getDescription(),
            'created_at' => $source->getCreatedAt(),
            'position' => $source->getPosition(),
            'level' => $source->getLevel(),
            'left' => $source->getLeft(),
            'right' => $source->getRight(),
            'parent_taxon' => $this->getParent($source, $targetClass),
        ];
        $target = new $targetClass();
        foreach ($values as $property => $value) {
            $this->propertyAccessor->setValue($target, $property, $value);
        }

        return $target;
    }

    private function getParent(TaxonInterface $taxon, string $targetClass): ?object
    {
        $parent = $taxon->getParent();
        if (null === $parent) {
            return null;
        }
        $locale = $taxon->getTranslation()->getLocale();
        if (null !== $locale) {
            $parent->setCurrentLocale($locale);
        }

        return $this->map($parent, $targetClass);
    }
}
