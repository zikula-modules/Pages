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

namespace Zikula\PagesModule\Block\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Helper\EntityDisplayHelper;

/**
 * Detail block form type base class.
 */
abstract class AbstractItemBlockType extends AbstractType
{
    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;

    public function __construct(
        EntityFactory $entityFactory,
        EntityDisplayHelper $entityDisplayHelper
    ) {
        $this->entityFactory = $entityFactory;
        $this->entityDisplayHelper = $entityDisplayHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addObjectTypeField($builder, $options);
        $this->addIdField($builder, $options);
        $this->addTemplateField($builder, $options);
    }

    /**
     * Adds an object type field.
     */
    public function addObjectTypeField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('objectType', HiddenType::class, [
            'label' => 'Object type:',
            'empty_data' => 'page'
        ]);
    }

    /**
     * Adds a item identifier field.
     */
    public function addIdField(FormBuilderInterface $builder, array $options = []): void
    {
        $repository = $this->entityFactory->getRepository($options['object_type']);
        // select without joins
        $entities = $repository->selectWhere('', '', false);
    
        $choices = [];
        foreach ($entities as $entity) {
            $choices[$this->entityDisplayHelper->getFormattedTitle($entity)] = $entity->getKey();
        }
        ksort($choices);
    
        $builder->add('id', ChoiceType::class, [
            'multiple' => false,
            'expanded' => false,
            'choices' => /** @Ignore */$choices,
            'required' => true,
            'label' => 'Entry to display:'
        ]);
    }

    /**
     * Adds template fields.
     */
    public function addTemplateField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('customTemplate', TextType::class, [
                'label' => 'Custom template:',
                'required' => false,
                'attr' => [
                    'maxlength' => 80,
                    /** @Ignore */
                    'title' => /** @Translate */'Example' . ': displaySpecial.html.twig'
                ],
                /** @Ignore */
                'help' => [
                    /** @Translate */'Example' . ': <code>displaySpecial.html.twig</code>',
                    /** @Translate */'Needs to be located in the "External/YourEntity/" directory.'
                ],
                'help_html' => true
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulapagesmodule_detailblock';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'object_type' => 'page'
            ])
            ->setRequired(['object_type'])
            ->setAllowedTypes('object_type', 'string')
        ;
    }
}
