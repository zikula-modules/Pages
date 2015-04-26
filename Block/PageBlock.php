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
 * Class PageBlock
 * @package Zikula\PagesModule\Block
 */
class PageBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ZikulaPagesModule:pageblock:', 'Block title::');
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
            'text_type' => $this->__('Show page'),
            'text_type_long' => $this->__('Show a page in a block'),
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
        if (!SecurityUtil::checkPermission('ZikulaPagesModule:pageblock:', "{$blockinfo['title']}::", ACCESS_READ)) {
            return false;
        }
        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        // return if no pid
        if (empty($vars['pid'])) {
            return false;
        }
        // get the page
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pageid' => $vars['pid']));
        // check for a valid item
        if (!$item) {
            return false;
        }
        if (!SecurityUtil::checkPermission($this->name . '::', "{$item['title']}::{$item['pageid']}", ACCESS_READ)) {
            return false;
        }
        // Create output object
        if (!isset($item['content'])) {
            return false;
        }
        // create the output object
        $this->view->setCacheId($item['pageid']);
        // assign the item
        $this->view->assign($item);
        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch('Block/pageblock_display.tpl');

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
        // create the output object
        $this->view->setCaching(false);
        // Get current content and assign it
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        $this->view->assign($vars);
        // Get all pages and assign them
        $pages = ModUtil::apiFunc($this->name, 'user', 'getall');
        $this->view->assign('pages', $pages);

        return $this->view->fetch('Block/pageblock_modify.tpl');
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
        // get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        // alter the corresponding variable
        $vars['pid'] = (int) FormUtil::getPassedValue('pid', null, 'POST');
        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);
        // clear the block cache
        $this->view->clear_cache('Block/pageslist.tpl');

        return $blockinfo;
    }

}