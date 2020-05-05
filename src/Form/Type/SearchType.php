<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class SearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query', TextType::class, [
                'required' => true,
                'label' => 'monsieurbiz_searchplugin.form.query',
                'constraints' => [
                    new NotBlank(),
                    new Required(),
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'submit'],
                'label' => 'monsieurbiz_searchplugin.form.submit',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'monsieurbiz_searchplugin_search';
    }
}
