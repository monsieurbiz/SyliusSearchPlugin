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

namespace MonsieurBiz\SyliusSearchPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SymfonySearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class SearchType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', SymfonySearchType::class, [
                'required' => true,
                'label' => 'monsieurbiz_searchplugin.form.query',
                'attr' => [
                    'placeholder' => 'monsieurbiz_searchplugin.form.query_placeholder',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Required(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'submit'],
                'label' => 'monsieurbiz_searchplugin.form.submit',
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'monsieurbiz_searchplugin_search';
    }
}
