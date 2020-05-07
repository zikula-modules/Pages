<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Form\Type\QuickNavigation\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\PagesModule\Helper\FeatureActivationHelper;
use Zikula\PagesModule\Helper\ListEntriesHelper;

/**
 * Page quick navigation form type base class.
 */
abstract class AbstractPageQuickNavType extends AbstractType
{
    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    /**
     * @var FeatureActivationHelper
     */
    protected $featureActivationHelper;

    public function __construct(
        ListEntriesHelper $listHelper,
        FeatureActivationHelper $featureActivationHelper
    ) {
        $this->listHelper = $listHelper;
        $this->featureActivationHelper = $featureActivationHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('all', HiddenType::class)
            ->add('own', HiddenType::class)
            ->add('tpl', HiddenType::class)
        ;

        if ($this->featureActivationHelper->isEnabled(FeatureActivationHelper::CATEGORIES, 'page')) {
            $this->addCategoriesField($builder, $options);
        }
        $this->addListFields($builder, $options);
        $this->addLanguageFields($builder, $options);
        $this->addSearchField($builder, $options);
        $this->addSortingFields($builder, $options);
        $this->addAmountField($builder, $options);
        $this->addBooleanFields($builder, $options);
        $builder->add('updateview', SubmitType::class, [
            'label' => 'OK',
            'attr' => [
                'class' => 'btn-secondary btn-sm'
            ]
        ]);
    }

    /**
     * Adds a categories field.
     */
    public function addCategoriesField(FormBuilderInterface $builder, array $options = []): void
    {
        $objectType = 'page';
        $entityCategoryClass = 'Zikula\PagesModule\Entity\\' . ucfirst($objectType) . 'CategoryEntity';
        $builder->add('categories', CategoriesType::class, [
            'label' => 'Category',
            'empty_data' => null,
            'attr' => [
                'class' => 'form-control-sm category-selector',
                'title' => 'This is an optional filter.'
            ],
            'required' => false,
            'multiple' => false,
            'module' => 'ZikulaPagesModule',
            'entity' => ucfirst($objectType) . 'Entity',
            'entityCategoryClass' => $entityCategoryClass,
            'showRegistryLabels' => true
        ]);
    }

    /**
     * Adds list fields.
     */
    public function addListFields(FormBuilderInterface $builder, array $options = []): void
    {
        $listEntries = $this->listHelper->getEntries('page', 'workflowState');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('workflowState', ChoiceType::class, [
            'label' => 'State',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => $choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => false,
            'expanded' => false
        ]);
    }

    /**
     * Adds language fields.
     */
    public function addLanguageFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('pageLanguage', LanguageType::class, [
            'label' => 'Page language',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All'
        ]);
    }

    /**
     * Adds a search field.
     */
    public function addSearchField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('q', SearchType::class, [
            'label' => 'Search',
            'attr' => [
                'maxlength' => 255,
                'class' => 'form-control-sm'
            ],
            'required' => false
        ]);
    }


    /**
     * Adds sorting fields.
     */
    public function addSortingFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('sort', ChoiceType::class, [
                'label' => 'Sort by',
                'attr' => [
                    'class' => 'form-control-sm'
                ],
                'choices' => [
                    'Title' => 'title',
                    'Page language' => 'pageLanguage',
                    'Content' => 'content',
                    'Counter' => 'counter',
                    'Active' => 'active',
                    'Display wrapper' => 'displayWrapper',
                    'Display title' => 'displayTitle',
                    'Display created' => 'displayCreated',
                    'Display updated' => 'displayUpdated',
                    'Display text info' => 'displayTextInfo',
                    'Display print' => 'displayPrint',
                    'Creation date' => 'createdDate',
                    'Creator' => 'createdBy',
                    'Update date' => 'updatedDate',
                    'Updater' => 'updatedBy'
                ],
                'required' => true,
                'expanded' => false
            ])
            ->add('sortdir', ChoiceType::class, [
                'label' => 'Sort direction',
                'empty_data' => 'asc',
                'attr' => [
                    'class' => 'form-control-sm'
                ],
                'choices' => [
                    'Ascending' => 'asc',
                    'Descending' => 'desc'
                ],
                'required' => true,
                'expanded' => false
            ])
        ;
    }

    /**
     * Adds a page size field.
     */
    public function addAmountField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('num', ChoiceType::class, [
            'label' => 'Page size',
            'empty_data' => 20,
            'attr' => [
                'class' => 'form-control-sm text-right'
            ],
            /** @Ignore */
            'choices' => [
                5 => 5,
                10 => 10,
                15 => 15,
                20 => 20,
                30 => 30,
                50 => 50,
                100 => 100
            ],
            'required' => false,
            'expanded' => false
        ]);
    }

    /**
     * Adds boolean fields.
     */
    public function addBooleanFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('active', ChoiceType::class, [
            'label' => 'Active',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('displayWrapper', ChoiceType::class, [
            'label' => 'Display wrapper',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('displayTitle', ChoiceType::class, [
            'label' => 'Display title',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('displayCreated', ChoiceType::class, [
            'label' => 'Display created',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('displayUpdated', ChoiceType::class, [
            'label' => 'Display updated',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('displayTextInfo', ChoiceType::class, [
            'label' => 'Display text info',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('displayPrint', ChoiceType::class, [
            'label' => 'Display print',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_pagequicknav';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'page'
        ]);
    }
}
