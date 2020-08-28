<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAttributeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductAttributeTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filterable', CheckboxType::class, [
                'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.filterable',
                'required' => true,
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
