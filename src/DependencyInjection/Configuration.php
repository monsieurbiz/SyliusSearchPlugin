<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('monsieur_biz_sylius_search');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('monsieur_biz_sylius_search');
        }

        $rootNode
            ->children()
            ->scalarNode('search_file')->end()
            ->scalarNode('instant_file')->end()
            ->scalarNode('taxon_file')->end()
            ->variableNode('documentable_classes')->end()
            ->arrayNode('limits')
                ->performNoDeepMerging()
                ->integerPrototype()->end()
                ->defaultValue([10, 25, 50])
                ->end()
            ->integerNode('search_default_limit')->defaultValue(10)->end()
            ->integerNode('instant_default_limit')->defaultValue(10)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
