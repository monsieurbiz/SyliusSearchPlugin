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

use JoliCode\Elastically\Mapping\YamlProvider;
use MonsieurBiz\SyliusSearchPlugin\Mapping\YamlWithLocaleProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

class AutowireMappingProviderParameterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(YamlProvider::class) || !$container->hasDefinition(YamlWithLocaleProvider::class)) {
            return;
        }

        $yamlMappingProvider = $container->getDefinition(YamlProvider::class);
        $decoratedYamlMappingProvider = $container->getDefinition(YamlWithLocaleProvider::class);

        try {
            $decoratedYamlMappingProvider->setArgument(
                '$configurationDirectory',
                $yamlMappingProvider->getArgument('$configurationDirectory')
            );
        } catch (OutOfBoundsException $exception) {
            // yaml provider service has no configuration directory argument
        }
    }
}
