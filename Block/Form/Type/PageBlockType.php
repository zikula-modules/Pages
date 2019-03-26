<?php

declare(strict_types=1);
/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\IdentityTranslator;

/**
 * Class PageBlockType
 */
class PageBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pid', ChoiceType::class, [
                'choices' => $options['pages'],
                'label' => $options['translator']->__('Page')
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
            'pages' => [],
            'translator' => new IdentityTranslator()
        ]);
    }
}
