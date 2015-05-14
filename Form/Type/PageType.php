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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Zikula\Module\CategoriesModule\Form\Type\CategoryType;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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

        $entityCategoryRegistries = \CategoryRegistryUtil::getRegisteredModuleCategories('ZikulaPagesModule', 'PageEntity', 'id');
        foreach ($entityCategoryRegistries as $registryId => $parentCategoryId) {
            $builder->add('categories', new CategoryType($registryId, $parentCategoryId), array('multiple' => true));
        }
    }

    public function getName()
    {
        return 'zikulapagesmodule_page';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Zikula\PagesModule\Entity\PageEntity',
        ));
    }

}