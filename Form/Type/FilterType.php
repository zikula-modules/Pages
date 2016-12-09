<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @see https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Zikula\PagesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startnum', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('orderby', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('sdir', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('language', 'Zikula\Bundle\FormExtensionBundle\Form\Type\LocaleType', [
                'attr' => ['class' => 'input-sm']
            ])
            ->add('filterButton', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'icon' => 'fa-filter fa-lg',
                'label' => __('Filter'),
                'attr' => ['class' => "btn btn-default btn-sm"]
            ])
            ->add('categoryAssignments', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
                'attr' => ['class' => 'input-sm'],
                'required' => false,
                'multiple' => false,
                'module' => 'ZikulaPagesModule',
                'entity' => 'PageEntity',
                'entityCategoryClass' => 'Zikula\PagesModule\Entity\CategoryAssignmentEntity',
            ]);
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
            'csrf_protection' => false,
            'entityCategoryRegistries' => [],
            'attr' => [
                'class' => 'form form-inline'
            ],
        ]);
    }
}
