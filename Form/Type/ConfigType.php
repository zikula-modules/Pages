<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Common\Translator\IdentityTranslator;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $builder
            ->add('enablecategorization', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Enable categorization'),
                'required' => false
            ])
            ->add('itemsperpage', 'Symfony\Component\Form\Extension\Core\Type\NumberType', [
                'label' =>  $translator->__('Items per page'),
                'constraints' => [new Assert\GreaterThan(['value' => 0])]
            ])
            ->add('def_displaywrapper', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Display additional information'),
                'required' => false
            ])
            ->add('def_displaytitle', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Display page title'),
                'required' => false
            ])
            ->add('def_displaycreated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Display page creation date'),
                'required' => false
            ])
            ->add('def_displayupdated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Display page update date'),
                'required' => false
            ])
            ->add('def_displaytextinfo', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Display page text statistics'),
                'required' => false
            ])
            ->add('def_displayprint', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Display page print link'),
                'required' => false
            ])
            ->add('addcategorytitletopermalink', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Add category title to permalink'),
                'required' => false,
                'disabled' => true
            ])
            ->add('showpermalinkinput', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' =>  $translator->__('Show permalink input field'),
                'required' => false,
                'disabled' => true
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save')
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel')
            ])
            ;
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_config';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator(),
        ]);
    }
}
