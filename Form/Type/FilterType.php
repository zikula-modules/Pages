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
use Zikula\Common\Translator\IdentityTranslator;

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
                'label' => $options['translator']->__('Filter'),
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
            'translator' => new IdentityTranslator(),
            'csrf_protection' => false,
            'attr' => [
                'class' => 'form form-inline'
            ],
        ]);
    }
}
