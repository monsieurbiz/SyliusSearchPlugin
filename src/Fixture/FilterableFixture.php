<?php
declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class FilterableFixture extends AbstractResourceFixture
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'monsieurbiz_sylius_search_filterable';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
            ->scalarNode('attribute')->end()
            ->scalarNode('option')->end()
            ->booleanNode('filterable')->defaultValue(true)->end()
        ;
    }
}
