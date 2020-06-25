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

namespace Zikula\PagesModule\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\Bundle\FormExtensionBundle\Form\Type\LocaleType;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Entity\PageCategoryEntity;
use Zikula\PagesModule\Helper\FeatureActivationHelper;
use Zikula\PagesModule\Helper\ListEntriesHelper;
use Zikula\PagesModule\Traits\ModerationFormFieldsTrait;

/**
 * Page editing form type base class.
 */
abstract class AbstractPageType extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    use ModerationFormFieldsTrait;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    /**
     * @var LocaleApiInterface
     */
    protected $localeApi;

    /**
     * @var FeatureActivationHelper
     */
    protected $featureActivationHelper;

    public function __construct(
        RequestStack $requestStack,
        EntityFactory $entityFactory,
        ListEntriesHelper $listHelper,
        LocaleApiInterface $localeApi,
        FeatureActivationHelper $featureActivationHelper
    ) {
        $this->requestStack = $requestStack;
        $this->entityFactory = $entityFactory;
        $this->listHelper = $listHelper;
        $this->localeApi = $localeApi;
        $this->featureActivationHelper = $featureActivationHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder, $options);
        if ($this->featureActivationHelper->isEnabled(FeatureActivationHelper::CATEGORIES, 'page')) {
            $this->addCategoriesField($builder, $options);
        }
        $this->addModerationFields($builder, $options);
        $this->addSubmitButtons($builder, $options);
    }

    /**
     * Adds basic entity fields.
     */
    public function addEntityFields(FormBuilderInterface $builder, array $options = []): void
    {
        
        $builder->add('title', TextType::class, [
            'label' => 'Title:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the title of the page.'
            ],
            'required' => true,
        ]);
        
        $builder->add('metaDescription', TextType::class, [
            'label' => 'Meta description:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the meta description of the page.'
            ],
            'required' => false,
        ]);
        
        $builder->add('pageLanguage', LocaleType::class, [
            'label' => 'Page language:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Choose the page language of the page.'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => /** @Ignore */$this->localeApi->getSupportedLocaleNames(),
            'choice_loader' => null,
        ]);
        
        $builder->add('content', TextareaType::class, [
            'label' => 'Content:',
            'help' => 'Note: this value must not exceed %length% characters.',
            'help_translation_parameters' => ['%length%' => 2000],
            'empty_data' => '',
            'attr' => [
                'maxlength' => 2000,
                'class' => '',
                'title' => 'Enter the content of the page.'
            ],
            'required' => true,
        ]);
        
        $builder->add('counter', IntegerType::class, [
            'label' => 'Counter:',
            'empty_data' => 0,
            'attr' => [
                'maxlength' => 11,
                'class' => '',
                'title' => 'Enter the counter of the page. Only digits are allowed.'
            ],
            'required' => false,
        ]);
        
        $builder->add('active', CheckboxType::class, [
            'label' => 'Active:',
            'label_attr' => [
                'class' => 'switch-custom'
            ],
            'attr' => [
                'class' => '',
                'title' => 'active ?'
            ],
            'required' => false,
        ]);
        
        $builder->add('displayWrapper', CheckboxType::class, [
            'label' => 'Display wrapper:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display additional information'
            ],
            'help' => 'Display additional information',
            'attr' => [
                'class' => '',
                'title' => 'display wrapper ?'
            ],
            'required' => false,
        ]);
        
        $builder->add('displayTitle', CheckboxType::class, [
            'label' => 'Display title:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page title'
            ],
            'help' => 'Display page title',
            'attr' => [
                'class' => '',
                'title' => 'display title ?'
            ],
            'required' => false,
        ]);
        
        $builder->add('displayCreated', CheckboxType::class, [
            'label' => 'Display created:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page creation date'
            ],
            'help' => 'Display page creation date',
            'attr' => [
                'class' => '',
                'title' => 'display created ?'
            ],
            'required' => false,
        ]);
        
        $builder->add('displayUpdated', CheckboxType::class, [
            'label' => 'Display updated:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page update date'
            ],
            'help' => 'Display page update date',
            'attr' => [
                'class' => '',
                'title' => 'display updated ?'
            ],
            'required' => false,
        ]);
        
        $builder->add('displayTextInfo', CheckboxType::class, [
            'label' => 'Display text info:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page text statistics'
            ],
            'help' => 'Display page text statistics',
            'attr' => [
                'class' => '',
                'title' => 'display text info ?'
            ],
            'required' => false,
        ]);
        
        $builder->add('displayPrint', CheckboxType::class, [
            'label' => 'Display print:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page print link'
            ],
            'help' => 'Display page print link',
            'attr' => [
                'class' => '',
                'title' => 'display print ?'
            ],
            'required' => false,
        ]);
        
        $helpText = /** @Translate */'You can input a custom permalink for the page or let this field free to create one automatically.';
        $builder->add('slug', TextType::class, [
            'label' => 'Permalink:',
            'required' => false,
            'attr' => [
                'maxlength' => 255,
                'class' => 'validate-unique',
                /** @Ignore */
                'title' => $helpText
            ],
            /** @Ignore */
            'help' => $helpText
        ]);
    }

    /**
     * Adds a categories field.
     */
    public function addCategoriesField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('categories', CategoriesType::class, [
            'label' => 'Category:',
            'empty_data' => null,
            'attr' => [
                'class' => 'category-selector'
            ],
            'required' => false,
            'multiple' => false,
            'module' => 'ZikulaPagesModule',
            'entity' => 'PageEntity',
            'entityCategoryClass' => PageCategoryEntity::class,
            'showRegistryLabels' => true
        ]);
    }

    /**
     * Adds submit buttons.
     */
    public function addSubmitButtons(FormBuilderInterface $builder, array $options = []): void
    {
        foreach ($options['actions'] as $action) {
            $builder->add($action['id'], SubmitType::class, [
                /** @Ignore */
                'label' => $action['title'],
                'icon' => 'delete' === $action['id'] ? 'fa-trash-alt' : '',
                'attr' => [
                    'class' => $action['buttonClass']
                ]
            ]);
            if ('create' === $options['mode'] && 'submit' === $action['id']) {
                // add additional button to submit item and return to create form
                $builder->add('submitrepeat', SubmitType::class, [
                    'label' => 'Submit and repeat',
                    'icon' => 'fa-repeat',
                    'attr' => [
                        'class' => $action['buttonClass']
                    ]
                ]);
            }
        }
        $builder->add('reset', ResetType::class, [
            'label' => 'Reset',
            'icon' => 'fa-sync',
            'attr' => [
                'formnovalidate' => 'formnovalidate'
            ]
        ]);
        $builder->add('cancel', SubmitType::class, [
            'label' => 'Cancel',
            'validate' => false,
            'icon' => 'fa-times'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_page';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                // define class for underlying data (required for embedding forms)
                'data_class' => PageEntity::class,
                'translation_domain' => 'page',
                'empty_data' => function (FormInterface $form) {
                    return $this->entityFactory->createPage();
                },
                'error_mapping' => [
                ],
                'mode' => 'create',
                'actions' => [],
                'has_moderate_permission' => false,
                'allow_moderation_specific_creator' => false,
                'allow_moderation_specific_creation_date' => false,
            ])
            ->setRequired(['mode', 'actions'])
            ->setAllowedTypes('mode', 'string')
            ->setAllowedTypes('actions', 'array')
            ->setAllowedTypes('has_moderate_permission', 'bool')
            ->setAllowedTypes('allow_moderation_specific_creator', 'bool')
            ->setAllowedTypes('allow_moderation_specific_creation_date', 'bool')
            ->setAllowedValues('mode', ['create', 'edit'])
        ;
    }
}