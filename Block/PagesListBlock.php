<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\PagesModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\PagesModule\Manager\PageCollectionManager;

/**
 * Class PagesListBlock
 * @package Zikula\PagesModule\Block
 */
class PagesListBlock extends AbstractBlockHandler
{
    /**
     * Display block.
     *
     * @param array $properties
     *
     * @return string|void The rendered block.
     */
    public function display(array $properties)
    {
        // Security check
        if (!$this->hasPermission('ZikulaPagesModule:pageslistblock:', "{$properties['title']}::", ACCESS_READ)) {
            return false;
        }
        // Defaults
        if (empty($properties['numitems'])) {
            $properties['numitems'] = 5;
        }

        // Call the modules API to get the items
        $pagesManager = new PageCollectionManager($this->get('doctrine.entitymanager'));
        $pagesManager->setItemsPerPage($properties['numitems']);
        $pagesManager->setOrder('lu_date', 'DESC');
        $pages = $pagesManager->get();

        // Check for no items returned
        if (empty($pages)) {
            return false;
        }
        // Call the modules API to get the numitems
        $countitems = count($pages);
        // Compare the numitems with the block setting
        if ($countitems <= $properties['numitems']) {
            $properties['numitems'] = $countitems;
        }

        // Display each item, permissions permitting
        $pageArray = array();
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        foreach ($pages as $page) {
            if ($this->hasPermission('ZikulaPagesModule' . '::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_OVERVIEW)) {
                if ($this->hasPermission('ZikulaPagesModule' . '::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_READ)) {
                    $pageArray[] = array(
                        'url' => $this->get('router')->generate('zikulapagesmodule_user_display', array('urltitle' => $page->getUrltitle())),
                        'title' => $page['title']
                    );
                } else {
                    $pageArray[] = array('title' => $page['title']);
                }
            }
        }

        return $this->renderView('@ZikulaPagesModule/Block/pagesListDisplay.html.twig', array('pages' => $pageArray));
    }

    public function getFormClassName()
    {
        return 'Zikula\PagesModule\Block\Form\Type\PagesListBlockType';
    }
}