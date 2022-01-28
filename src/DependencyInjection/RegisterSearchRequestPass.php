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

class RegisterSearchRequestPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('monsieurbiz.search.registry.search_request')) {
            return;
        }

        $registry = $container->getDefinition('monsieurbiz.search.registry.search_request');
        foreach ($container->findTaggedServiceIds('monsieurbiz.search.request') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('register', [$tag['id'] ?? $serviceId, new Reference($serviceId)]);
            }
        }
    }
}
