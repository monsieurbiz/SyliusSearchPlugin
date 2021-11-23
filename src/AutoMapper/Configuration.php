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

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper;

final class Configuration
{
    private array $sourceClasses = [];

    public function addSourceClass(string $identifier, string $className): void
    {
        $this->sourceClasses[$identifier] = $className;
    }

    public function getSourceClass($identifier): string
    {
        if (!\array_key_exists($identifier, $this->sourceClasses)) {
            throw new \Exception('Unknown source class for: ' . $identifier);
        }

        return $this->sourceClasses[$identifier];
    }
}
