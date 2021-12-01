<?php

/*
 * This file is part of SyliusSearchPlugin.
 *
 * (c) Monsieur Biz
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Form\Type\Settings;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LimitsSearchType extends AbstractSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $options['documentable'];

        $this->addWithDefaultCheckbox(
            $builder,
            'search',
            CollectionType::class,
            [
                'entry_type' => NumberType::class,
                'label' => $documentable->getIndexCode() . ' search',
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ]
        );
        $this->addWithDefaultCheckbox(
            $builder,
            'instant_search',
            CollectionType::class,
            [
                'entry_type' => NumberType::class,
                'label' => $documentable->getIndexCode() . ' instant_search',
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
