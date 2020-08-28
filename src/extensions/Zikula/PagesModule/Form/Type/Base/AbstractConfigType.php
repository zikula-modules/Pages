<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 *
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio 1.5.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\PagesModule\Form\Type\Field\MultiListType;
use Zikula\PagesModule\AppSettings;
use Zikula\PagesModule\Helper\ListEntriesHelper;

/**
 * Configuration form type base class.
 */
abstract class AbstractConfigType extends AbstractType
{

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    public function __construct(
        ListEntriesHelper $listHelper
    ) {
        $this->listHelper = $listHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addGeneralSettingsFields($builder, $options);
        $this->addListViewsFields($builder, $options);
        $this->addModerationFields($builder, $options);
        $this->addIntegrationFields($builder, $options);

        $this->addSubmitButtons($builder, $options);
    }

    /**
     * Adds fields for general settings fields.
     */
    public function addGeneralSettingsFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('enableCategorisation', CheckboxType::class, [
            'label' => 'Enable categorisation:',
            'label_attr' => [
                'class' => 'switch-custom',
            ],
            'attr' => [
                'class' => '',
                'title' => 'The enable categorisation option',
            ],
            'required' => false,
        ]);
        $builder->add('displayWrapper', CheckboxType::class, [
            'label' => 'Display wrapper:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display additional information',
            ],
            'help' => 'Display additional information',
            'attr' => [
                'class' => '',
                'title' => 'The display wrapper option',
            ],
            'required' => false,
        ]);
        $builder->add('displayTitle', CheckboxType::class, [
            'label' => 'Display title:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page title',
            ],
            'help' => 'Display page title',
            'attr' => [
                'class' => '',
                'title' => 'The display title option',
            ],
            'required' => false,
        ]);
        $builder->add('displayCreated', CheckboxType::class, [
            'label' => 'Display created:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page creation date',
            ],
            'help' => 'Display page creation date',
            'attr' => [
                'class' => '',
                'title' => 'The display created option',
            ],
            'required' => false,
        ]);
        $builder->add('displayUpdated', CheckboxType::class, [
            'label' => 'Display updated:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page update date',
            ],
            'help' => 'Display page update date',
            'attr' => [
                'class' => '',
                'title' => 'The display updated option',
            ],
            'required' => false,
        ]);
        $builder->add('displayTextInfo', CheckboxType::class, [
            'label' => 'Display text info:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page text statistics',
            ],
            'help' => 'Display page text statistics',
            'attr' => [
                'class' => '',
                'title' => 'The display text info option',
            ],
            'required' => false,
        ]);
        $builder->add('displayPrint', CheckboxType::class, [
            'label' => 'Display print:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Display page print link',
            ],
            'help' => 'Display page print link',
            'attr' => [
                'class' => '',
                'title' => 'The display print option',
            ],
            'required' => false,
        ]);
    }

    /**
     * Adds fields for list views fields.
     */
    public function addListViewsFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('pageEntriesPerPage', IntegerType::class, [
            'label' => 'Page entries per page:',
            'label_attr' => [
                'class' => 'tooltips',
                'title' => 'The amount of pages shown per page.',
            ],
            'help' => 'The amount of pages shown per page.',
            'empty_data' => 10,
            'attr' => [
                'maxlength' => 11,
                'class' => '',
                'title' => 'Enter the page entries per page. Only digits are allowed.',
            ],
            'required' => true,
        ]);
        $builder->add('pagePrivateMode', CheckboxType::class, [
            'label' => 'Page private mode:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Whether users may only see own pages.',
            ],
            'help' => 'Whether users may only see own pages.',
            'attr' => [
                'class' => '',
                'title' => 'The page private mode option',
            ],
            'required' => false,
        ]);
        $builder->add('showOnlyOwnEntries', CheckboxType::class, [
            'label' => 'Show only own entries:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Whether only own entries should be shown on view pages by default or not.',
            ],
            'help' => 'Whether only own entries should be shown on view pages by default or not.',
            'attr' => [
                'class' => '',
                'title' => 'The show only own entries option',
            ],
            'required' => false,
        ]);
        $builder->add('filterDataByLocale', CheckboxType::class, [
            'label' => 'Filter data by locale:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Whether automatically filter data in the frontend based on the current locale or not.',
            ],
            'help' => 'Whether automatically filter data in the frontend based on the current locale or not.',
            'attr' => [
                'class' => '',
                'title' => 'The filter data by locale option',
            ],
            'required' => false,
        ]);
    }

    /**
     * Adds fields for moderation fields.
     */
    public function addModerationFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('allowModerationSpecificCreatorForPage', CheckboxType::class, [
            'label' => 'Allow moderation specific creator for page:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Whether to allow moderators choosing a user which will be set as creator.',
            ],
            'help' => 'Whether to allow moderators choosing a user which will be set as creator.',
            'attr' => [
                'class' => '',
                'title' => 'The allow moderation specific creator for page option',
            ],
            'required' => false,
        ]);
        $builder->add('allowModerationSpecificCreationDateForPage', CheckboxType::class, [
            'label' => 'Allow moderation specific creation date for page:',
            'label_attr' => [
                'class' => 'tooltips switch-custom',
                'title' => 'Whether to allow moderators choosing a custom creation date.',
            ],
            'help' => 'Whether to allow moderators choosing a custom creation date.',
            'attr' => [
                'class' => '',
                'title' => 'The allow moderation specific creation date for page option',
            ],
            'required' => false,
        ]);
    }

    /**
     * Adds fields for integration fields.
     */
    public function addIntegrationFields(FormBuilderInterface $builder, array $options = []): void
    {
        $listEntries = $this->listHelper->getEntries('appSettings', 'enabledFinderTypes');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('enabledFinderTypes', MultiListType::class, [
            'label' => 'Enabled finder types:',
            'label_attr' => [
                'class' => 'tooltips',
                'title' => 'Which sections are supported in the Finder component (used by Scribite plug-ins).',
            ],
            'help' => 'Which sections are supported in the Finder component (used by Scribite plug-ins).',
            'empty_data' => [],
            'attr' => [
                'class' => '',
                'title' => 'Choose the enabled finder types.',
            ],
            'required' => false,
            'placeholder' => 'Choose an option',
            'choices' => /** @Ignore */$choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => true,
            'expanded' => false,
        ]);
    }

    /**
     * Adds submit buttons.
     */
    public function addSubmitButtons(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('save', SubmitType::class, [
            'label' => 'Update configuration',
            'icon' => 'fa-check',
            'attr' => [
                'class' => 'btn-success',
            ],
        ]);
        $builder->add('reset', ResetType::class, [
            'label' => 'Reset',
            'icon' => 'fa-sync',
            'attr' => [
                'formnovalidate' => 'formnovalidate',
            ],
        ]);
        $builder->add('cancel', SubmitType::class, [
            'label' => 'Cancel',
            'validate' => false,
            'icon' => 'fa-times',
            'attr' => [
                'formnovalidate' => 'formnovalidate',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // define class for underlying data
            'data_class' => AppSettings::class,
            'translation_domain' => 'config',
        ]);
    }
}
