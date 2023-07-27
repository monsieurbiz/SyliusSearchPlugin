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

namespace MonsieurBiz\SyliusSearchPlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductAttributeType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductAttributeTypeExtension extends AbstractTypeExtension
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $searchWeightValues = range(1, 10);

        $builder
            ->add('searchable', CheckboxType::class, [
                'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.searchable',
                'required' => true,
            ])
            ->add('filterable', CheckboxType::class, [
                'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.filterable',
                'required' => true,
            ])
            ->add('search_weight', ChoiceType::class, [
                'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.search_weight',
                'required' => true,
                'choices' => array_combine($searchWeightValues, $searchWeightValues),
            ])
        ;
    }

    public static function getExtendedTypes(): array
    {
        return [
            ProductAttributeType::class,
        ];
    }
}
