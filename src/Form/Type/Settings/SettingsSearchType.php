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

namespace MonsieurBiz\SyliusSearchPlugin\Form\Type\Settings;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsSearchType extends AbstractSettingsType
{
    private ServiceRegistryInterface $documentableRegistry;

    public function __construct(ServiceRegistryInterface $documentableRegistry)
    {
        $this->documentableRegistry = $documentableRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DocumentableInterface $documentable */
        foreach ($this->documentableRegistry->all() as $documentable) {
            $this->addWithDefaultCheckbox(
                $builder,
                'instant_search_enabled__' . $documentable->getIndexCode(),
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'monsieurbiz_searchplugin.admin.setting_form.instant_search_enabled_' . $documentable->getIndexCode(),
                ]
            );
            /** @var array $optionsData */
            $optionsData = $options['data'] ?? [];
            $subOptions = [];
            $subOptions['data'] = $optionsData['limits__' . $documentable->getIndexCode()] ?? [];
            $subOptions['documentable'] = $documentable;
            $this->addWithDefaultCheckbox(
                $builder,
                'limits__' . $documentable->getIndexCode(),
                LimitsSearchType::class,
                $subOptions
            );
        }
    }
}
