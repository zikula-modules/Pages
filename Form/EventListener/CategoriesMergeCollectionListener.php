<?php

namespace Zikula\PagesModule\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class CategoriesMergeCollectionListener
 * @package Zikula\Core\Forms\EventListener
 */
class CategoriesMergeCollectionListener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(FormEvents::SUBMIT => 'onBindNormData');
    }

    public function onBindNormData(FormEvent $event)
    {
        $submittedData = $event->getData();
        $rootEntity = $event->getForm()->getParent()->getData();

        $collection = new ArrayCollection();
        foreach ($submittedData as $categoryCollectionByRegistry) {
            foreach ($categoryCollectionByRegistry as $category) {
                    $category->setEntity($rootEntity);
                    $collection->add($category);
            }
        }

        $event->setData($collection);
    }
}
