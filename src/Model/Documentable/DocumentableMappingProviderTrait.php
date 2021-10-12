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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use JoliCode\Elastically\Mapping\MappingProviderInterface;

trait DocumentableMappingProviderTrait
{
    protected MappingProviderInterface $mappingProvider;

    public function setMappingProvider(MappingProviderInterface $mapping): void
    {
        $this->mappingProvider = $mapping;
    }

    public function getMappingProvider(): MappingProviderInterface
    {
        return $this->mappingProvider;
    }
}
