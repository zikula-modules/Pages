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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\PagesModule\Manager\PageManager;
use SecurityUtil;
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
     * @throws AccessDeniedHttpException If the current user does not have adequate permissions to perform this function.
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
            throw new \InvalidArgumentException();
        }
        // Get the existing page
        $this->_page = new PageManager($this->getEntityManager());
        $this->_page->findById($pageid);
        $item = $this->_page->toArray();
        if ($item === false) {
            throw new NotFoundHttpException($this->__('No such page found.'));
        }
        if (!SecurityUtil::checkPermission($this->name . '::', $item['title'] . '::' . $pageid, ACCESS_DELETE)) {
            throw new AccessDeniedHttpException();
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
    
        $returnUrl = $view->getContainer()->get('router')->generate('zikulapagesmodule_admin_view');
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($returnUrl);
        }
        $pageid = $this->_page->getId();
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Page deleted.'));
        $this->notifyHooks(new Zikula_ProcessHook('pages.ui_hooks.pages.process_delete', $pageid));
        $this->_page->remove();
        return $view->redirect($returnUrl);
    }

}