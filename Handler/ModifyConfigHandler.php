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

use SecurityUtil;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * This class provides a handler to modify the module settings.
 */
class ModifyConfigHandler extends \Zikula_Form_AbstractHandler
{

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
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }
        $view->assign($this->getVars());
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
        if ($args['commandName'] == 'cancel') {
            $returnUrl = $view->getContainer()->get('router')->generate('zikulapagesmodule_admin_modifyconfig');
            return $view->redirect($returnUrl);
        }
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        // load form values
        $data = $view->getValues();
        if ($data['itemsperpage'] < 1) {
            $data['itemsperpage'] = 25;
        }
        $this->setVars($data);
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Module configuration updated.'));
        return true;
    }

}