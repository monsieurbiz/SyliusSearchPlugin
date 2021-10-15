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

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\Documentable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DocumentableRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('monsieurbiz.search.registry.documentable')) {
            return;
        }

        $registry = $container->getDefinition('monsieurbiz.search.registry.documentable');
        $documentables = $container->getParameter('monsieurbiz.search.config.documents');
        if (!\is_array($documentables)) {
            return;
        }
        foreach ($documentables as $indexCode => $documentableConfiguration) {
            $documentableServiceId = 'search.documentable.' . $indexCode;
            $documentableDefinition = (new Definition(Documentable::class)) // TODO - move into config
                ->setAutowired(true)
                ->setArguments([
                    '$indexCode' => $indexCode,
                    '$sourceClass' => $documentableConfiguration['source'],
                    '$targetClass' => $documentableConfiguration['target'],
                ])
            ;
            $documentableDefinition = $container->setDefinition($documentableServiceId, $documentableDefinition);
            $documentableDefinition->addTag('monsieurbiz.search.documentable');
            $documentableDefinition->addMethodCall('setMappingProvider', [new Reference($documentableConfiguration['mapping_provider'])]);
            $documentableDefinition->addMethodCall('setDatasource', [new Reference($documentableConfiguration['datasource'])]);

            // Add documentable into registry
            $registry->addMethodCall('register', [$documentableServiceId, new Reference($documentableServiceId)]);
        }
    }
}
