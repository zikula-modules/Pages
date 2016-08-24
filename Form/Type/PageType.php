<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Zikula\PagesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('urltitle', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'required' => false,
                'label' => __('PermaLink URL title')
            ])
            ->add($builder->create('metadescription', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'required' => false
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add($builder->create('metakeywords', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'required' => false
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add('content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType')
            ->add('displaywrapper', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Display additional information')
            ])
            ->add('displaytitle', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Display page title')
            ])
            ->add('displaycreated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Display page creation date')
            ])
            ->add('displayupdated', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Display page update date')
            ])
            ->add('displaytextinfo', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Display page text statistics')
            ])
            ->add('displayprint', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Display page print link')
            ])
            ->add($builder->create('language', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'required' => false,
                'placeholder' => __('All')
                ])->addModelTransformer(new NullToEmptyTransformer())
            )
            ->add('obj_status', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => __('Page is active')
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
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
            'data_class' => 'Zikula\PagesModule\Entity\PageEntity',
        ]);
    }
}
