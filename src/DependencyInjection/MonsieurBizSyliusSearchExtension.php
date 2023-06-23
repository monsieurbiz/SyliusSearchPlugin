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

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class MonsieurBizSyliusSearchExtension extends Extension
{
    public const EXTENSION_CONFIG_NAME = 'monsieurbiz.search.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        foreach ($config as $name => $value) {
            $container->setParameter(self::EXTENSION_CONFIG_NAME . '.' . $name, $value);
            if ('documents' === $name) {
                $this->addDocumentsConfiguration(self::EXTENSION_CONFIG_NAME . '.' . $name, $value, $container);
            }
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(RequestInterface::class)
            ->addTag('monsieurbiz.search.request')
        ;
    }

    /**
     * @inheritdoc
     */
    public function getAlias(): string
    {
        return str_replace(['monsieur_biz'], ['monsieurbiz'], parent::getAlias());
    }

    private function addDocumentsConfiguration(string $name, array $values, ContainerBuilder $container): void
    {
        foreach ($values as $documentIndexName => $documentValues) {
            $this->addDocumentConfiguration($name . '.' . $documentIndexName, $documentValues, $container);
        }
    }

    private function addDocumentConfiguration(string $name, array $values, ContainerBuilder $container): void
    {
        foreach ($values as $configName => $configValue) {
            $container->setParameter($name . '.' . $configName, $configValue);
        }
    }
}
