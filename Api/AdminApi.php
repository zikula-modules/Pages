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

namespace Zikula\PagesModule\Api;

use SecurityUtil;

/**
 * Class AdminApi
 * @package Zikula\PagesModule\Api
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array();
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $links[] = array('url' => $this->get('router')->generate('zikulapagesmodule_admin_index'), 'text' => $this->__('Pages list'), 'icon' => 'list');
        }
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADD)) {
            $links[] = array('url' => $this->get('router')->generate('zikulapagesmodule_adminform_edit'), 'text' => $this->__('New Page'), 'icon' => 'plus');
        }
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => $this->get('router')->generate('zikulapagesmodule_admin_purge'), 'text' => $this->__('Purge permalinks'), 'icon' => 'refresh');
            $links[] = array('url' => $this->get('router')->generate('zikulapagesmodule_adminform_config'), 'text' => $this->__('Modify Config'), 'icon' => 'wrench');
        }

        return $links;
    }

}