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

namespace MonsieurBiz\SyliusSearchPlugin\DependencyInjection;

use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\PrefixedDocumentableInterface;
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
        $documentables = $container->getParameter('monsieurbiz.search.config.documents');
        if (!\is_array($documentables)) {
            return;
        }

        // Sort documentables by position
        uasort($documentables, function ($documentableA, $documentableB) {
            return $documentableA['position'] <=> $documentableB['position'];
        });

        $this->addDocumentableServices($container, $documentables);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateDocumentableResource(string $class): void
    {
        $interfaces = (array) class_implements($class);

        if (!\in_array(DocumentableInterface::class, $interfaces, true)) {
            throw new InvalidArgumentException(\sprintf('Class "%s" must implement "%s" to be registered as a Documentable.', $class, DocumentableInterface::class));
        }
    }

    private function isPrefixedDocumentableClass(string $class): bool
    {
        $interfaces = (array) class_implements($class);

        return \in_array(PrefixedDocumentableInterface::class, $interfaces, true);
    }

    private function addDocumentableServices(ContainerBuilder $container, array $documentables): void
    {
        $registry = $container->getDefinition('monsieurbiz.search.registry.documentable');

        $searchSettings = [];
        if ($container->hasParameter('monsieurbiz.settings.config.plugins')) {
            /** @var array $searchSettings */
            $searchSettings = $container->getParameter('monsieurbiz.settings.config.plugins');
        }

        foreach ($documentables as $indexCode => $documentableConfiguration) {
            $documentableServiceId = 'search.documentable.' . $indexCode;

            // Create documentable service
            $this->createDocumentable($container, $documentableServiceId, $indexCode, $documentableConfiguration);

            // Add documentable into registry
            $registry->addMethodCall('register', [$documentableServiceId, new Reference($documentableServiceId)]);

            // Add the default settings value of documentable
            $searchSettings['monsieurbiz.search']['default_values']['search_enabled__' . $indexCode] = $documentableConfiguration['search_enabled'];
            $searchSettings['monsieurbiz.search']['default_values']['instant_search_enabled__' . $indexCode] = $documentableConfiguration['instant_search_enabled'];
            $searchSettings['monsieurbiz.search']['default_values']['limits__' . $indexCode] = $documentableConfiguration['limits'];
        }

        $container->setParameter('monsieurbiz.settings.config.plugins', $searchSettings);
    }

    private function createDocumentable(ContainerBuilder $container, string $documentableServiceId, string $indexCode, array $documentableConfiguration): void
    {
        $documentableClass = $documentableConfiguration['document_class'];
        $this->validateDocumentableResource($documentableClass);
        $documentableDefinition = (new Definition($documentableClass))
            ->setAutowired(true)
            ->setArguments([
                '$indexCode' => $indexCode,
                '$sourceClass' => $documentableConfiguration['source'],
                '$targetClass' => $documentableConfiguration['target'],
                '$templates' => $documentableConfiguration['templates'],
                '$limits' => $documentableConfiguration['limits'],
            ])
        ;
        $documentableDefinition = $container->setDefinition($documentableServiceId, $documentableDefinition);
        $documentableDefinition->addTag('monsieurbiz.search.documentable');
        $documentableDefinition->addMethodCall('setMappingProvider', [new Reference($documentableConfiguration['mapping_provider'])]);
        $documentableDefinition->addMethodCall('setDatasource', [new Reference($documentableConfiguration['datasource'])]);
        if ($this->isPrefixedDocumentableClass($documentableClass) && isset($documentableConfiguration['prefix'])) {
            $documentableDefinition->addMethodCall('setPrefix', [$documentableConfiguration['prefix']]);
        }
    }
}
