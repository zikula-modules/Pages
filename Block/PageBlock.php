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
use SecurityUtil;
use Zikula\Core\Controller\AbstractBlockController;
use Zikula\PagesModule\Manager\PageCollectionManager;

/**
 * Class PageBlock
 * @package Zikula\PagesModule\Block
 */
class PageBlock extends AbstractBlockController
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
            'text_type' => __('Show page'),
            'text_type_long' => __('Show a page in a block'),
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
        if (!SecurityUtil::checkPermission('ZikulaPagesModule:pageblock:', "{$blockInfo['title']}::", ACCESS_READ)) {
            return false;
        }
        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // return if no pid
        if (empty($vars['pid'])) {
            return false;
        }
        // get the page
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        $page = $this->get('doctrine.entitymanager')->getRepository('\Zikula\PagesModule\Entity\PageEntity')->find($vars['pid']);
        // check for a valid item
        if (!$page) {
            return false;
        }
        if (!SecurityUtil::checkPermission($this->name . '::', "{$page->getTitle()}::{$page->getPageId()}", ACCESS_READ)) {
            return false;
        }

        $blockInfo['content'] = $this->render('ZikulaPagesModule:Block:pageBlockDisplay.html.twig', array('content' => $page->getContent()))->getContent();

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
        // Get current content and assign it
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        $vars['pid'] = !empty($vars['pid']) ? $vars['pid'] : '';

        // Get all pages and assign them
        $pages = new PageCollectionManager($this->get('doctrine.entitymanager'));
        $vars['pages'] = $pages->get();

        return $this->render('ZikulaPagesModule:Block:pageBlockModify.html.twig', $vars)->getContent();
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
        // get current content
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // alter the corresponding variable
        $vars['pid'] = $this->get('request')->request->get('pid', null);
        // write back the new contents
        $blockInfo['content'] = BlockUtil::varsToContent($vars);

        return $blockInfo;
    }

}