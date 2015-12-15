<?php

namespace Zikula\PagesModule\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\PagesModule\Form\DataTransformer\CategoriesCollectionTransformer; // @todo use core version
use Zikula\PagesModule\Form\EventListener\CategoriesMergeCollectionListener; // @todo use core version

class CategoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['entityCategoryClass']) || empty($options['module']) || empty($options['entity'])) {
            throw new \InvalidArgumentException('empty argument!');
        }

        $registries = \CategoryRegistryUtil::getRegisteredModuleCategories($options['module'], $options['entity'], 'id');

        foreach($registries as $registryId => $categoryId) {

            $builder->add(
                'registry_' . $registryId,
                'entity',
                [
                    'required' => $options['required'],
                    'multiple' => $options['multiple'],
                    'class' => 'ZikulaCategoriesModule:CategoryEntity',
                    'property' => 'name',
                    'query_builder' => function(EntityRepository $repo) use($categoryId) {
                        //TODO: (move to)/use own entity repository after CategoryUtil migration
                        return $repo->createQueryBuilder('e')
                                    ->where('e.parent = :parentId')
                                    ->setParameter('parentId', (int) $categoryId);
                    }
                ]);
        }

        $builder->addViewTransformer(new CategoriesCollectionTransformer($options), true);
        $builder->addEventSubscriber(new CategoriesMergeCollectionListener());

    }

    public function getName()
    {
        return 'categories';
    }

    public function getBlockPrefix()
    {
        return 'categories';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form form-inline',
            ],
            'multiple' => false,
            'module' => '',
            'entity' => '',
            'entityCategoryClass' => ''
        ]);
    }
}
