<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
            ->add('title', 'text')
            ->add('urltitle', 'text', array('required' => false, 'label' => __('PermaLink URL title')))
            ->add($builder->create('metadescription', 'textarea', array('required' => false))
                ->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('metakeywords', 'textarea', array('required' => false))
                ->addModelTransformer(new NullToEmptyTransformer()))
            ->add('content')
            ->add('displaywrapper', 'checkbox', array('required' => false, 'label' => __('Display additional information')))
            ->add('displaytitle', 'checkbox', array('required' => false, 'label' => __('Display page title')))
            ->add('displaycreated', 'checkbox', array('required' => false, 'label' => __('Display page creation date')))
            ->add('displayupdated', 'checkbox', array('required' => false, 'label' => __('Display page update date')))
            ->add('displaytextinfo', 'checkbox', array('required' => false, 'label' => __('Display page text statistics')))
            ->add('displayprint', 'checkbox', array('required' => false, 'label' => __('Display page print link')))
            ->add($builder->create('language', 'choice', array(
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'required' => false,
                'placeholder' => __('All')
                ))->addModelTransformer(new NullToEmptyTransformer()))
            ->add('obj_status', 'checkbox', array('required' => false, 'label' => __('Page is active')))
            ->add('save', 'submit', array('label' => 'Create Page'));

        // @todo in Symfony 2.8 use FqCn string 'Zikula\Core\Forms\Type\CategoriesType' (or corrected namespace)
        $builder->add('categories', new CategoriesType(), [
            'required' => false,
            'multiple' => true,
            'module' => 'ZikulaPagesModule',
            'entity' => 'PageEntity',
            'entityCategoryClass' => 'Zikula\PagesModule\Entity\CategoryEntity',
        ]);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getName()
    {
        return 'zikulapagesmodule_page';
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