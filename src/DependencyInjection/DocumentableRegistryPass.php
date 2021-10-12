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

namespace MonsieurBiz\SyliusSearchPlugin\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DocumentableRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition('monsieurbiz.search.registry.documentable');
        $documentableIds = array_keys($container->findTaggedServiceIds('monsieurbiz.search.documentable'));
        foreach ($documentableIds as $documentableId) {
            $registry->addMethodCall('register', [$documentableId, new Reference($documentableId)]);
        }
    }
}
