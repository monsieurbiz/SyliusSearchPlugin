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

/** A mapping registered with the search document mapper. */
interface DocumentMappingInterface extends DocumentMapperInterface
{
    /** @param class-string $targetClass */
    public function supports(object $source, string $targetClass): bool;
}
