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

final class AutomapperConfigurationRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $automapperConfig = $container->getDefinition(\MonsieurBiz\SyliusSearchPlugin\AutoMapper\Configuration::class);
        $automapperClasses = $container->getParameter('monsieurbiz.search.config.automapper_classes');
        foreach ($automapperClasses as $identifier => $sourceClass) {
            $automapperConfig->addMethodCall('addSourceClass', [$identifier, $sourceClass]);
        }
    }
}