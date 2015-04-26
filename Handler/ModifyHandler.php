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

namespace Zikula\PagesModule\Handler;

use FormUtil;
use Zikula\PagesModule\Manager\PageManager;
use LogUtil;
use SecurityUtil;
use Zikula_Exception_Forbidden;
use CategoryRegistryUtil;
use ModUtil;
use Zikula_Hook_ValidationProviders;
use Zikula_ValidationHook;
use ZLanguage;
use Zikula\Core\RouteUrl;
use Zikula_ProcessHook;
use System;

/**
 * This class provides a handler to modify or create a page.
 */
class ModifyHandler extends \Zikula_Form_AbstractHandler
{

    /**
     * Page.
     *
     * When set this handler is in edit mode.
     *
     * @var PageManager
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
        $pageid = FormUtil::getPassedValue('pageid', isset($args['pageid']) ? $args['pageid'] : null, 'GET');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
        // At this stage we check to see if we have been passed $objectid
        if (!empty($objectid)) {
            $pageid = $objectid;
        }
        // Get the page
        $this->_page = new PageManager($this->getEntityManager());
        if (empty($pageid)) {
            $this->_page->create();
        } else {
            $this->_page->find(array('pageid' => $pageid));
        }
        $item = $this->_page->toArray();
        if ($item === false) {
            return LogUtil::registerError($this->__('No such page found.'), 404);
        }
        if (!SecurityUtil::checkPermission($this->name . '::', $item['title'] . '::' . $pageid, ACCESS_EDIT)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        if ($this->getVar('enablecategorization', true)) {
            // load and assign registred categories
            $categories = CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'Page');
            $view->assign('registries', $categories);
        }
        // assign the item to the template
        $view->assign($item);
        $view->assign('page', $this->_page->get());
        if (!empty($pageid)) {
            // now we've got this far let's lock the page for editing
            $params = array('lockName' => "Pagespage{$pageid}", 'returnUrl' => $view->getContainer()->get('router')->generate('zikulapagesmodule_admin_view'));
            ModUtil::apiFunc('PageLock', 'user', 'pageLock', $params);
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
        if ($this->_page->get()) {
            // DO NOT REMOVE!! This is important to be called before ->getValues() below, although
            // `$this->_page` still contains the reference to the page. However, due to Doctrine-related
            // behaviour, this referenced is outdated and must be refreshed. There are problems with saving
            // the categories otherwise.
            $pageId = $this->_page->getId();
            if (!empty($pageId)) {
                $this->_page->findById($pageId);
            }
            $view->assign('page', $this->_page->get());
        }
        // load form values
        $data = $view->getValues();
        $data['pageid'] = $this->_page->getId();
        if ($args['commandName'] == 'cancel') {
            // now release the page lock
            ModUtil::apiFunc('PageLock', 'user', 'releaseLock', array('lockName' => "Pagespage{$data['pageid']}"));
            $url = $view->getContainer()->get('router')->generate('zikulapagesmodule_admin_view');
            return $view->redirect($url);
        } else {
            if ($args['commandName'] == 'remove') {
                // now release the page lock
                ModUtil::apiFunc('PageLock', 'user', 'releaseLock', array('lockName' => "Pagespage{$data['pageid']}"));
                $this->_page->remove();
                $url = $view->getContainer()->get('router')->generate('zikulapagesmodule_admin_view');
                return $view->redirect($url);
            }
        }
        // check for valid form
        if (!$view->isValid()) {
            // Do NOT release Lock.
            return LogUtil::registerError('Validation failed!');
        }
        $ok = $this->_page->set($data);
        if (!$ok) {
            return LogUtil::registerError('Page save failed!');
        }
        $data['pageid'] = $this->_page->getid();
        //this line is needed for new pages
        $validators = $this->notifyHooks(new Zikula_ValidationHook('pages.ui_hooks.pages.validate_edit', new Zikula_Hook_ValidationProviders()))->getValidators();
        if ($validators->hasErrors()) {
            return LogUtil::registerError('Hook validation failed!');
        }
        // Success
        LogUtil::registerStatus($this->__('Done! Page updated.'));
        $url = new RouteUrl('zikulapagesmodule_user_display', array('pageid' => $data['pageid']));
        $this->notifyHooks(new Zikula_ProcessHook('pages.ui_hooks.pages.process_edit', $data['pageid'], $url));
        // now release the page lock
        ModUtil::apiFunc('PageLock', 'user', 'releaseLock', array('lockName' => "Pagespage{$data['pageid']}"));
        $returnUrl = ModUtil::url($this->name, 'user', 'display', array('pageid' => $data['pageid']));
        return \System::redirect($returnUrl);
    }

}