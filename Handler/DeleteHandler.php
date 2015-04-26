<?php
/**
 * Copyright Pages Team 2012
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

namespace Zikula\PagesModule\Handler;

use FormUtil;
use LogUtil;
use Zikula\PagesModule\Access\PageAccess;
use SecurityUtil;
use Zikula_Exception_Forbidden;
use ModUtil;
use Zikula_ProcessHook;

/**
 * This class provides a handler to delete a page.
 */
class DeleteHandler extends \Zikula_Form_AbstractHandler
{

    /**
     * Page.
     *
     * When set this handler is in edit mode.
     *
     * @var PageAccess
     */
    private $_page;
    /**
     * Initialise the form handler
     *
     * @param \Zikula_Form_View $view Reference to Form render object.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(\Zikula_Form_View $view)
    {
    
        $pageid = FormUtil::getPassedValue('pageid', isset($args['pageid']) ? $args['pageid'] : null, 'REQUEST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        if (!empty($objectid)) {
            $pageid = $objectid;
        }
        // Validate the essential parameters
        if (empty($pageid)) {
            return LogUtil::registerArgsError();
        }
        // Get the existing page
        $this->_page = new PageAccess($this->getEntityManager());
        $this->_page->findById($pageid);
        $item = $this->_page->toArray();
        if ($item === false) {
            return LogUtil::registerError($this->__('No such page found.'), 404);
        }
        if (!SecurityUtil::checkPermission($this->name . '::', $item['title'] . '::' . $pageid, ACCESS_DELETE)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        return true;
    }
    
    /**
     * Handle form submission.
     *
     * @param \Zikula_Form_View $view  Reference to Form render object.
     * @param array            &$args Arguments of the command.
     *
     * @return boolean|void
     */
    public function handleCommand(\Zikula_Form_View $view, &$args)
    {
    
        $returnUrl = ModUtil::url($this->name, 'admin', 'view');
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($returnUrl);
        }
        $pageid = $this->_page->getId();
        LogUtil::registerStatus($this->__('Done! Page deleted.'));
        $this->notifyHooks(new Zikula_ProcessHook('pages.ui_hooks.pages.process_delete', $pageid));
        $this->_page->remove();
        return $view->redirect($returnUrl);
    }

}