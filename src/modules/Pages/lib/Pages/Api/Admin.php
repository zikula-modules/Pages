<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: Admin.php 434 2010-07-06 12:53:16Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Value_Addons
 * @subpackage Pages
 */

class Pages_Api_Admin extends Zikula_Api
{
    /**
     * create a new page
     * @param $args['title'] name of the item
     * @param $args['content'] content of the item
     * @param $args['language'] language of the item
     * @return mixed page ID on success, false on failure
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['title']) || !isset($args['content'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!SecurityUtil::checkPermission('Pages::', "$args[title]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // defaults
        if (!isset($args['language'])) {
            $args['language'] = '';
        }
        if (!isset($args['displaywrapper'])) {
            $args['displaywrapper'] = 0;
        }
        if (!isset($args['displaytitle'])) {
            $args['displaytitle'] = 0;
        }
        if (!isset($args['displaycreated'])) {
            $args['displaycreated'] = 0;
        }
        if (!isset($args['displayupdated'])) {
            $args['displayupdated'] = 0;
        }
        if (!isset($args['displaytextinfo'])) {
            $args['displaytextinfo'] = 0;
        }
        if (!isset($args['displayprint'])) {
            $args['displayprint'] = 0;
        }

        // define the permalink title if not present
        $urltitlecreatedfromtitle = false;
        if (!isset($args['urltitle']) || empty($args['urltitle'])) {
            $args['urltitle'] = DataUtil::formatPermalink($args['title']);
            $urltitlecreatedfromtitle = true;
        }

        if (ModUtil::apiFunc('pages', 'admin', 'checkuniquepermalink', $args) == false) {
            $args['urltitle'] = '';
            if ($urltitlecreatedfromtitle == true) {
                LogUtil::registerStatus($this->__('The permalinks retrieved from the title has to be unique!'));
            } else {
                LogUtil::registerStatus($this->__('The permalink has to be unique!'));
            }
            LogUtil::registerStatus($this->__('The permalink has been removed, please update the page with a correct and unique permalink'));
        }

        if (!DBUtil::insertObject($args, 'pages', 'pageid')) {
            return LogUtil::registerError($this->__('Error! Creation attempt failed.'));
        }

        // Let any hooks know that we have created a new item.
        $this->callHooks('item', 'create', $args['pageid'], array('module' => 'Pages'));

        // An item was created, so we clear all cached pages of the items list.
        $render = Zikula_View::getInstance('Pages');
        $render->clear_cache('pages_user_view.htm');

        // Return the id of the newly created item to the calling process
        return $args['pageid'];
    }

    /**
     * delete a page
     * @param $args['pageid'] ID of the page
     * @return bool true on success, false on failure
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['pageid'])) {
            return LogUtil::registerArgsError();
        }

        // Check item exists before attempting deletion
        $item = ModUtil::apiFunc('Pages', 'user', 'get', array('pageid' => $args['pageid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('No such page found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Pages::', "$item[title]::$item[pageid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        if (!DBUtil::deleteObjectByID('pages', $args['pageid'], 'pageid')) {
            return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
        }

        // Let any hooks know that we have deleted an item.
        $this->callHooks('item', 'delete', $args['pageid'], array('module' => 'Pages'));

        // The item has been modified, so we clear all cached pages of this item.
        $render = Zikula_View::getInstance('Pages');
        $render->clear_cache(null, $args['pageid']);
        $render->clear_cache('pages_user_view.htm');

        return true;
    }

    /**
     * update a page
     * @param $args['pageid'] the ID of the page
     * @param $args['title'] the new name of the item
     * @param $args['content'] the new content of the item
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['pageid']) ||
                !isset($args['title']) ||
                !isset($args['content'])) {
            return LogUtil::registerArgsError();
        }

        // Check page to update exists, and get information for
        // security check
        $item = ModUtil::apiFunc('Pages', 'user', 'get', array('pageid' => $args['pageid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('No such page found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Pages::', "$item[title]::$item[pageid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::checkPermission('Pages::', "$args[title]::$item[pageid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // set some defaults
        if (!isset($args['language'])) {
            $args['language'] = '';
        }
        if (!isset($args['displaywrapper'])) {
            $args['displaywrapper'] = 0;
        }
        if (!isset($args['displaytitle'])) {
            $args['displaytitle'] = 0;
        }
        if (!isset($args['displaycreated'])) {
            $args['displaycreated'] = 0;
        }
        if (!isset($args['displayupdated'])) {
            $args['displayupdated'] = 0;
        }
        if (!isset($args['displaytextinfo'])) {
            $args['displaytextinfo'] = 0;
        }
        if (!isset($args['displayprint'])) {
            $args['displayprint'] = 0;
        }

        // define the permalink title if not present
        $urltitlecreatedfromtitle = false;
        if (!isset($args['urltitle']) || empty($args['urltitle'])) {
            $args['urltitle'] = DataUtil::formatPermalink($args['title']);
            $urltitlecreatedfromtitle = true;
        }

        if (ModUtil::apiFunc('pages', 'admin', 'checkuniquepermalink', $args) == false) {
            $args['urltitle'] = '';
            if($urltitlecreatedfromtitle == true) {
                LogUtil::registerStatus($this->__('The permalinks retrieved from the title has to be unique!'));
            } else {
                LogUtil::registerStatus($this->__('The permalink has to be unique!'));
            }
            LogUtil::registerStatus($this->__('The permalink has been removed, please update the page with a correct and unique permalink'));
        }

        if (!DBUtil::updateObject($args, 'pages', '', 'pageid')) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.'));
        }

        // Let any other modules know we have updated an item
        $this->callHooks('item', 'update', $args['pageid'], array('module' => 'Pages'));

        // The item has been modified, so we clear all cached pages of this item.
        $render = Zikula_View::getInstance('Pages');
        $render->clear_cache(null, $args['pageid']);
        $render->clear_cache(null, $item['urltitle']);
        $render->clear_cache('pages_user_view.htm');

        return true;
    }

    /**
     * Purge the permalink fields in the Pages table
     * @author Mateo Tibaquira
     * @return bool true on success, false on failure
     */
    public function purgepermalinks($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Pages::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // disable categorization to do this (if enabled)
        $catenabled = ModUtil::getVar('Pages', 'enablecategorization');
        if ($catenabled) {
            $this->setVar('enablecategorization', false);
            ModUtil::dbInfoLoad('Pages', 'Pages', true);
        }

        // get all the ID and permalink of the table
        $data = DBUtil::selectObjectArray('pages', '', '', -1, -1, 'pageid', null, null, array('pageid', 'urltitle'));

        // loop the data searching for non equal permalinks
        $perma = '';
        foreach (array_keys($data) as $pageid) {
            $perma = strtolower(DataUtil::formatPermalink($data[$pageid]['urltitle']));
            if ($data[$pageid]['urltitle'] != $perma) {
                $data[$pageid]['urltitle'] = $perma;
            } else {
                unset($data[$pageid]);
            }
        }

        // restore the categorization if was enabled
        if ($catenabled) {
            $this->setVar('enablecategorization', true);
        }

        if (empty($data)) {
            return true;
            // store the modified permalinks
        } elseif (DBUtil::updateObjectArray($data, 'pages', 'pageid')) {
            // let the calling process know that we have finished successfully
            return true;
        } else {
            return false;
        }
    }

    /**
     * get available admin panel links
     *
     * @author Mark West
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Pages::', '::', ACCESS_READ)) {
            $links[] = array('url'  => ModUtil::url('Pages', 'admin', 'view'),
                    'text' => $this->__('View pages list'));
        }
        if (SecurityUtil::checkPermission('Pages::', '::', ACCESS_ADD)) {
            $links[] = array('url'  => ModUtil::url('Pages', 'admin', 'newitem'),
                    'text' => $this->__('Create a page'));
        }
        if (SecurityUtil::checkPermission('Pages::', '::', ACCESS_ADMIN)) {
            $links[] = array('url'  => ModUtil::url('Pages', 'admin', 'view', array('purge' => 1)),
                    'text' => $this->__('Purge permalinks'));
            $links[] = array('url'  => ModUtil::url('Pages', 'admin', 'modifyconfig'),
                    'text' => $this->__('Settings'));
        }

        return $links;
    }

    /**
     * check if a permalink is unique for Pages
     *
     * @author Frank Schummertz
     * @return boolean true if permalink is unique, otherwise false
     */
    public function checkuniquepermalink($args)
    {
        $pntable  = DBUtil::getTables();
        $pagescol = $pntable['pages_column'];
        $where    = "WHERE $pagescol[urltitle] = '" . DataUtil::formatForStore($args['urltitle']) . "'";
        if (isset($args['pageid']) && $args['pageid']) {
            $where .= " AND $pagescol[pageid] != " . DataUtil::formatForStore((int)($args['pageid']));
        }

        $count = DBUtil::selectObjectCount('pages', $where);
        return !((bool)$count);
    }
}