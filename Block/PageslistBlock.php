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

use SecurityUtil;
use BlockUtil;
use ModUtil;
use FormUtil;

/**
 * Class PageslistBlock
 * @package Zikula\PagesModule\Block
 */
class PageslistBlock extends \Zikula_Controller_AbstractBlock
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
            'text_type' => $this->__('Pages list'),
            'text_type_long' => $this->__('Display a list of pages'),
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
     * @param array $blockinfo A blockinfo structure.
     *
     * @return string|void The rendered block.
     */
    public function display($blockinfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPagesModule:pageslistblock:', "{$blockinfo['title']}::", ACCESS_READ)) {
            return false;
        }
        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        // Defaults
        if (empty($vars['numitems'])) {
            $vars['numitems'] = 5;
        }
        // Check if the module is available.
        if (!ModUtil::available($this->name)) {
            return false;
        }
        // Call the modules API to get the items
        $items = ModUtil::apiFunc($this->name, 'user', 'getall');
        // Check for no items returned
        if (empty($items)) {
            return false;
        }
        // Call the modules API to get the numitems
        $countitems = ModUtil::apiFunc($this->name, 'user', 'countitems');
        // Compare the numitems with the block setting
        if ($countitems <= $vars['numitems']) {
            $vars['numitems'] = $countitems;
        }
        // Create output object
        $this->view->setCacheId($blockinfo['bid']);
        // Display each item, permissions permitting
        $shownResults = 0;
        $pagesitems = array();
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission($this->name . '::', "{$item['title']}::{$item['pageid']}", ACCESS_OVERVIEW)) {
                $shownResults++;
                if ($shownResults <= $vars['numitems']) {
                    if (SecurityUtil::checkPermission($this->name . '::', "{$item['title']}::{$item['pageid']}", ACCESS_READ)) {
                        $pagesitems[] = array('url' => ModUtil::url($this->name, 'user', 'display', array('pageid' => $item['pageid'])), 'title' => $item['title']);
                    } else {
                        $pagesitems[] = array('title' => $item['title']);
                    }
                }
            }
        }
        $this->view->assign('items', $pagesitems);
        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch('Block/pageslist.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
    
    /**
     * modify block settings
     *
     * @param array $blockinfo a blockinfo structure
     *
     * @return string the block form
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        // Defaults
        if (empty($vars['numitems'])) {
            $vars['numitems'] = 5;
        }
        // Create output object
        $this->view->setCaching(false);
        // assign the appropriate values
        $this->view->assign($vars);

        return $this->view->fetch('Block/pageslist_modify.tpl');
    }
    
    /**
     * update block settings
     *
     * @param array $blockinfo A blockinfo structure.
     *
     * @return array The modified blockinfo structure.
     */
    public function update($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        // alter the corresponding variable
        $vars['numitems'] = (int) FormUtil::getPassedValue('numitems', null, 'POST');
        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);
        // clear the block cache
        $this->view->clear_cache('Block/pageslist.tpl');

        return $blockinfo;
    }

}