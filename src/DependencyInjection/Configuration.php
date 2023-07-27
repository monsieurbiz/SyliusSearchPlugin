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

use MonsieurBiz\SyliusSearchPlugin\Mapping\YamlWithLocaleProvider;
use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\RepositoryDatasource;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\Documentable;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('monsieur_biz_sylius_search');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('documents')
                    ->useAttributeAsKey('code', false)
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('prefix')->defaultValue(null)->end()
                            ->scalarNode('document_class')->defaultValue(Documentable::class)->end()
                            ->scalarNode('instant_search_enabled')->defaultValue(false)->end()
                            ->scalarNode('source')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('target')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('mapping_provider')->defaultValue(YamlWithLocaleProvider::class)->end()
                            ->scalarNode('datasource')->defaultValue(RepositoryDatasource::class)->end()
                            ->arrayNode('templates')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('item')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('instant')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()

                            // Limits
                            ->arrayNode('limits')
                                ->performNoDeepMerging()
                                ->useAttributeAsKey('type')
                                ->defaultValue(['search' => [9, 18, 27], 'taxon' => [9, 18, 27], 'instant_search' => [10]])
                                ->prototype('array')
                                ->prototype('scalar')->end()
                                ->end()
                            ->end()

                            // Position
                            ->integerNode('position')->defaultValue(0)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('automapper_classes')
                    ->children()
                        ->arrayNode('sources')
                            ->useAttributeAsKey('code', false)
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('targets')
                            ->useAttributeAsKey('code', false)
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('elastically_configuration_paths')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
