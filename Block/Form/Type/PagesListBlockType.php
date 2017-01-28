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

/**
 * Class PagesListBlockType
 */
class PagesListBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numitems', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => __('Number of pages to display')
            ])
        ;
    }

    public function getName()
    {
        return 'zikulapagesmodule_pageslistblock';
    }
}
