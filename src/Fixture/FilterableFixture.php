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

namespace MonsieurBiz\SyliusSearchPlugin\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Fixture\Factory\FilterableFixtureFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class FilterableFixture extends AbstractResourceFixture implements FilterableFixtureInterface
{
    /**
     * FilterableFixture constructor.
     */
    public function __construct(
        EntityManagerInterface $productManager,
        FilterableFixtureFactoryInterface $exampleFactory
    ) {
        parent::__construct($productManager, $exampleFactory);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'monsieurbiz_sylius_search_filterable';
    }

    /**
     * @inheritdoc
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
