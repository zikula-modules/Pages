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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Zikula\CategoriesModule\Form\Type\CategoryType;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startnum', 'hidden')
            ->add('orderby', 'hidden')
            ->add('sdir', 'hidden')
            ->add('language', 'zikula_locale', array('attr' => array('class' => 'input-sm')))
            ->add('filterButton', 'submit', array(
                'icon' => 'fa-filter fa-lg',
                'label' => __('Filter'),
                'attr' => array('class' => "btn btn-default btn-sm")
            ));
        // @todo This is a Symfony 2.8 method of getting the formType
        $builder->add('categories', 'Zikula\Core\Forms\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
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
        return 'zikulapagesmodule_filter';
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_filter';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entityCategoryRegistries' => [],
            'attr' => [
                'class' => 'form form-inline'
            ],
        ]);
    }
}