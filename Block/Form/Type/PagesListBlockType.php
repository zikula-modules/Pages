<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\PagesModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PagesListBlockType
 * @package Zikula\PagesModule\Block\Form\Type
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