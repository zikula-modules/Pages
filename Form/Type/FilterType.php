<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\Form\Type\LocaleType;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\PagesModule\Entity\CategoryAssignmentEntity;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startnum', HiddenType::class)
            ->add('orderby', HiddenType::class)
            ->add('sdir', HiddenType::class)
            ->add('language', LocaleType::class, [
                'choices' => $options['locales'],
                'attr' => ['class' => 'input-sm'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
            ])
            ->add('filterButton', SubmitType::class, [
                'icon' => 'fa-filter fa-lg',
                'label' => $options['translator']->__('Filter'),
                'attr' => ['class' => "btn btn-default btn-sm"]
            ])
            ->add('categoryAssignments', CategoriesType::class, [
                'attr' => ['class' => 'input-sm'],
                'required' => false,
                'multiple' => false,
                'module' => 'ZikulaPagesModule',
                'entity' => 'PageEntity',
                'entityCategoryClass' => CategoryAssignmentEntity::class,
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
            'locales' => [],
            'translator' => new IdentityTranslator(),
            'csrf_protection' => false,
            'attr' => [
                'class' => 'form form-inline'
            ],
        ]);
    }
}
