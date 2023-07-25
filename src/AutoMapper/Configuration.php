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

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper;

use RuntimeException;

final class Configuration implements ConfigurationInterface
{
    private array $sourceClasses;

    private array $targetClasses;

    public function __construct(
        array $automapperClasses
    ) {
        $this->sourceClasses = $automapperClasses['sources'] ?? [];
        $this->targetClasses = $automapperClasses['targets'] ?? [];
    }

    /**
     * @deprecated you should not be able to define any mapping outside the config. If necessary, define your own configuration class
     */
    public function addSourceClass(string $identifier, string $className): void
    {
        $this->sourceClasses[$identifier] = $className;
    }

    public function getSourceClass(string $identifier): string
    {
        if (!\array_key_exists($identifier, $this->sourceClasses)) {
            throw new RuntimeException('Unknown source class for: ' . $identifier);
        }

        return $this->sourceClasses[$identifier];
    }

    /**
     * @deprecated you should not be able to define any mapping outside the config. If necessary, define your own configuration class
     */
    public function addTargetClass(string $identifier, string $className): void
    {
        $this->targetClasses[$identifier] = $className;
    }

    public function getTargetClass(string $identifier): string
    {
        if (!\array_key_exists($identifier, $this->targetClasses)) {
            throw new RuntimeException('Unknown target class for: ' . $identifier);
        }

        return $this->targetClasses[$identifier];
    }
}
