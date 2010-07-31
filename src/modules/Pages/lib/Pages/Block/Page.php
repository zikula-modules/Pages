<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org/
 * @version $Id: Page.php 434 2010-07-06 12:53:16Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Andrea Moro
 * @author Mark West
 * @package Zikula_Value_Addons
 * @subpackage Pages
 */

class Pages_Block_Page extends Zikula_Block
{
    /**
     * initialise block
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('Pages:pageblock:', 'Block title::');
    }

    /**
     * get information on block
     */
    public function info()
    {
        return array('module'          => 'Pages',
                'text_type'       => $this->__('Show page'),
                'text_type_long'  => $this->__('Show a page in a block'),
                'allow_multiple'  => true,
                'form_content'    => false,
                'form_refresh'    => false,
                'show_preview'    => true,
                'admin_tableless' => true);
    }

    /**
     * display block
     */
    public function display($blockinfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Pages:pageblock:', "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // return if no pid
        if (empty($vars['pid'])) {
            return;
        }

        // get the page
        $item = ModUtil::apiFunc('Pages', 'user', 'get', array('pageid' => $vars['pid']));

        // check for a valid item
        if (!$item) {
            return;
        }

        if (!SecurityUtil::checkPermission('Pages::', "{$item['title']}::{$item['pageid']}", ACCESS_READ)) {
            return;
        }

        // Create output object
        if (!isset($item['content'])) {
            return;
        }

        // create the output object
        $this->view->setCache_Id($item['pageid']);

        // assign the item
        $this->view->assign($item);

        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch('pages_block_pageblock_display.htm');
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     */
    public function modify($blockinfo)
    {
        // create the output object
        $this->view->setCaching(false);

        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // assign the block vars
        $this->view->assign($vars);

        // Return output
        return $this->view->fetch('pages_block_pageblock_modify.htm');
    }

    /**
     * update block settings
     */
    public function update($blockinfo)
    {
        // get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // alter the corresponding variable
        $vars['pid'] = (int)FormUtil::getPassedValue('pid', null, 'POST');

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('pages_block_pageslist.htm');

        return $blockinfo;
    }
}