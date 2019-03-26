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

namespace Zikula\PagesModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\PagesModule\Block\Form\Type\PagesListBlockType;
use Zikula\PagesModule\Manager\PageCollectionManager;

/**
 * Class PagesListBlock
 */
class PagesListBlock extends AbstractBlockHandler
{
    /**
     * Display block.
     *
     * @param array $properties
     *
     * @return string|void The rendered block
     */
    public function display(array $properties)
    {
        // Security check
        if (!$this->hasPermission('ZikulaPagesModule:pageslistblock:', "{$properties['title']}::", ACCESS_READ)) {
            return;
        }
        // Defaults
        if (empty($properties['numitems'])) {
            $properties['numitems'] = 5;
        }

        // Call the modules API to get the items
        $pagesManager = new PageCollectionManager($this->get('doctrine')->getManager());
        $pagesManager->setItemsPerPage($properties['numitems']);
        $pagesManager->setOrder('lu_date', 'DESC');
        $pages = $pagesManager->get();

        // Check for no items returned
        if (empty($pages)) {
            return;
        }
        // Call the modules API to get the numitems
        $countitems = count($pages);
        // Compare the numitems with the block setting
        if ($countitems <= $properties['numitems']) {
            $properties['numitems'] = $countitems;
        }

        // Display each item, permissions permitting
        $pageArray = [];
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        foreach ($pages as $page) {
            if ($this->hasPermission('ZikulaPagesModule' . '::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_OVERVIEW)) {
                if ($this->hasPermission('ZikulaPagesModule' . '::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_READ)) {
                    $pageArray[] = [
                        'url' => $this->get('router')->generate('zikulapagesmodule_user_display', ['urltitle' => $page->getUrltitle()]),
                        'title' => $page['title']
                    ];
                } else {
                    $pageArray[] = ['title' => $page['title']];
                }
            }
        }

        return $this->renderView('@ZikulaPagesModule/Block/pagesListDisplay.html.twig', ['pages' => $pageArray]);
    }

    public function getFormClassName()
    {
        return PagesListBlockType::class;
    }

    public function getFormOptions()
    {
        return [
            'translator' => $this->getTranslator()
        ];
    }
}
