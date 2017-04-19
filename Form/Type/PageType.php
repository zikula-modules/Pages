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
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\Common\Translator\IdentityTranslator;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $builder
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('urltitle', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'required' => false,
                'label' =>  $translator->__('PermaLink URL title')
            ])
            ->add($builder->create('metadescription', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'required' => false
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add($builder->create('metakeywords', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'required' => false
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add('content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'attr' => ['rows' => '10'],
            ])
            ->add('displaywrapper', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Display additional information')
            ])
            ->add('displaytitle', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Display page title')
            ])
            ->add('displaycreated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Display page creation date')
            ])
            ->add('displayupdated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Display page update date')
            ])
            ->add('displaytextinfo', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Display page text statistics')
            ])
            ->add('displayprint', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Display page print link')
            ])
            ->add(
                $builder->create('language', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'choices' => $options['locales'],
                    'choices_as_values' => true,
                    'required' => false,
                    'placeholder' =>  $translator->__('All')
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add('obj_status', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' =>  $translator->__('Page is active')
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save')
            ])
            ->add('categoryAssignments', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
                'required' => false,
                'multiple' => true,
                'module' => 'ZikulaPagesModule',
                'entity' => 'PageEntity',
                'entityCategoryClass' => 'Zikula\PagesModule\Entity\CategoryAssignmentEntity',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_page';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator(),
            'data_class' => 'Zikula\PagesModule\Entity\PageEntity',
            'locales' => ['English' => 'en']
        ]);
    }
}
