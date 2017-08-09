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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\PagesModule\Entity\CategoryAssignmentEntity;
use Zikula\PagesModule\Entity\PageEntity;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $builder
            ->add('title', TextType::class)
            ->add('urltitle', TextType::class, [
                'required' => false,
                'label' =>  $translator->__('PermaLink URL title')
            ])
            ->add($builder->create('metadescription', TextareaType::class, [
                'required' => false
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add($builder->create('metakeywords', TextareaType::class, [
                'required' => false
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add('content', TextareaType::class, [
                'attr' => ['rows' => '10'],
            ])
            ->add('displaywrapper', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Display additional information')
            ])
            ->add('displaytitle', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Display page title')
            ])
            ->add('displaycreated', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Display page creation date')
            ])
            ->add('displayupdated', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Display page update date')
            ])
            ->add('displaytextinfo', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Display page text statistics')
            ])
            ->add('displayprint', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Display page print link')
            ])
            ->add(
                $builder->create('language', ChoiceType::class, [
                    'choices' => $options['locales'],
                    'choices_as_values' => true,
                    'required' => false,
                    'placeholder' =>  $translator->__('All')
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add('obj_status', CheckboxType::class, [
                'required' => false,
                'label' =>  $translator->__('Page is active')
            ])
            ->add('save', SubmitType::class, [
                'label' => $translator->__('Save')
            ])
            ->add('categoryAssignments', CategoriesType::class, [
                'required' => false,
                'multiple' => true,
                'module' => 'ZikulaPagesModule',
                'entity' => 'PageEntity',
                'entityCategoryClass' => CategoryAssignmentEntity::class,
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
            'data_class' => PageEntity::class,
            'locales' => ['English' => 'en']
        ]);
    }
}
