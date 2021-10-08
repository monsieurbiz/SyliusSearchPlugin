<?php

namespace MonsieurBiz\SyliusSearchPlugin\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DocumentableRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('monsieurbiz.search.registry.documentable');
        $documentableIds = array_keys($container->findTaggedServiceIds('monsieurbiz.search.documentable'));
        foreach ($documentableIds as $documentableId) {
            $registry->addMethodCall('register', [$documentableId, new Reference($documentableId)]);
        }
    }
}
