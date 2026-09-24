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

namespace MonsieurBiz\SyliusSearchPlugin\Mapper;

use InvalidArgumentException;

final class DocumentMapper implements DocumentMapperInterface
{
    /** @param iterable<DocumentMappingInterface> $mappings */
    public function __construct(private iterable $mappings)
    {
    }

    public function map(object $source, string $targetClass): object
    {
        foreach ($this->mappings as $mapping) {
            if ($mapping->supports($source, $targetClass)) {
                return $mapping->map($source, $targetClass);
            }
        }

        throw new InvalidArgumentException(\sprintf('No search mapping registered from %s to %s.', $source::class, $targetClass));
    }
}
