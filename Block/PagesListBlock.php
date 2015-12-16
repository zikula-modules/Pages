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

use BlockUtil;
use ModUtil;
use SecurityUtil;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Controller\AbstractBlockController;
use Zikula\PagesModule\Manager\PageCollectionManager;

/**
 * Class PagesListBlock
 * @package Zikula\PagesModule\Block
 */
class PagesListBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ZikulaPagesModule:pageslistblock:', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        return array(
            'module' => $this->name,
            'text_type' => __('Pages list'),
            'text_type_long' => __('Display a list of pages'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true,
            'admin_tableless' => true
        );
    }

    /**
     * Display block.
     *
     * @param array $blockInfo A blockInfo structure.
     *
     * @return string|void The rendered block.
     */
    public function display($blockInfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPagesModule:pageslistblock:', "{$blockInfo['title']}::", ACCESS_READ)) {
            return false;
        }
        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // Defaults
        if (empty($vars['numitems'])) {
            $vars['numitems'] = 5;
        }
        // Check if the module is available.
        if (!ModUtil::available($this->name)) {
            return false;
        }
        // Call the modules API to get the items
        $pagesManager = new PageCollectionManager($this->get('doctrine.entitymanager'));
        $pagesManager->setItemsPerPage($vars['numitems']);
        $pagesManager->setOrder('lu_date', 'DESC');
        $pages = $pagesManager->get();

        // Check for no items returned
        if (empty($pages)) {
            return false;
        }
        // Call the modules API to get the numitems
        $countitems = count($pages);
        // Compare the numitems with the block setting
        if ($countitems <= $vars['numitems']) {
            $vars['numitems'] = $countitems;
        }

        // Display each item, permissions permitting
        $pageArray = array();
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        foreach ($pages as $page) {
            if (SecurityUtil::checkPermission($this->name . '::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_OVERVIEW)) {
                if (SecurityUtil::checkPermission($this->name . '::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_READ)) {
                    $pageArray[] = array(
                        'url' => $this->get('router')->generate('zikulapagesmodule_user_display', array('urltitle' => $page->getUrltitle())),
                        'title' => $page['title']
                    );
                } else {
                    $pageArray[] = array('title' => $page['title']);
                }
            }
        }

        $blockInfo['content'] = $this->render('ZikulaPagesModule:Block:pagesListDisplay.html.twig', array('pages' => $pageArray))->getContent();

        return BlockUtil::themeBlock($blockInfo);
    }

    /**
     * modify block settings
     *
     * @param array $blockInfo a blockInfo structure
     *
     * @return string the block form
     */
    public function modify($blockInfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // Defaults
        $vars['numitems'] = !empty($vars['numitems']) ? $vars['numitems'] : 5;

        return $this->render('ZikulaPagesModule:Block:pagesListModify.html.twig', $vars)->getContent();
    }

    /**
     * update block settings
     *
     * @param array $blockInfo A blockInfo structure.
     *
     * @return array The modified blockInfo structure.
     */
    public function update($blockInfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // alter the corresponding variable
        $vars['numitems'] = $this->get('request')->request->get('numitems', null);
        // write back the new contents
        $blockInfo['content'] = BlockUtil::varsToContent($vars);


        return $blockInfo;
    }

    /**
     * @param $view
     * @param $parameters
     * @param Response|null $response
     * @return Response
     */
    private function render($view, $parameters, Response $response = null)
    {
        if ($this->has('templating')) {
            return $this->get('templating')->renderResponse($view, $parameters, $response);
        }

        return '';
    }
}