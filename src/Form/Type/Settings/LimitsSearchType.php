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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LimitsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $options['documentable'];

        $builder->add(
            'search',
            CollectionType::class,
            [
                'entry_type' => IntegerType::class,
                'label' => 'monsieurbiz_searchplugin.admin.setting_form.limit_search_' . $documentable->getIndexCode(),
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ]
        );
        $builder->add(
            'instant_search',
            CollectionType::class,
            [
                'entry_type' => IntegerType::class,
                'label' => 'monsieurbiz_searchplugin.admin.setting_form.limit_instant_search_' . $documentable->getIndexCode(),
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ]
        );
        $builder->add(
            'taxon',
            CollectionType::class,
            [
                'entry_type' => IntegerType::class,
                'label' => 'monsieurbiz_searchplugin.admin.setting_form.limit_taxon_' . $documentable->getIndexCode(),
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('documentable');
    }
}
