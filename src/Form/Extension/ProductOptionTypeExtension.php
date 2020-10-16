<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductOptionTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filterable', CheckboxType::class, [
                'label' => 'monsieurbiz_searchplugin.admin.product_option.form.filterable',
                'required' => true,
            ])
        ;
    }

    public static function getExtendedTypes(): array
    {
        return [
            ProductOptionType::class,
        ];
    }
}
