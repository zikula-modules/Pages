<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PageBlockType
 * @package Zikula\PagesModule\Block\Form\Type
 */
class PageBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pid', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['pages'],
                'label' => __('Page')
            ])
        ;
    }

    public function getName()
    {
        return 'zikulapagesmodule_pageblock';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'pages' => []
        ]);
    }
}
