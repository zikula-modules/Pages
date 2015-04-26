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
namespace Zikula\PagesModule\Controller;

use SecurityUtil;
use LogUtil;
use FormUtil;
use Pages_Access_Pages;
use ModUtil;
use ZLanguage;
use CategoryUtil;
use Pages_Access_Page;
use System;
/** @noinspection PhpDocSignatureInspection */
class UserController extends \Zikula_AbstractController
{

    /**
     * list all pages
     *
     * @param array $args Arguments.
     *
     * @return string html string
     */
    public function listPagesAction($args)
    {
    
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Pages::', '::', ACCESS_READ), LogUtil::getErrorMsgPermission());
        $startnum = (int) FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 1, 'GET');
        $this->view->assign('startnum', $startnum);
        $pages = new Pages_Access_Pages();
        $pages->setStartNumber($startnum);
        $pages->setOrder('title');
        $pages->enablePager();
        $this->view->assign('pages', $pages->get());
        // assign the values for the smarty plugin to produce a pager
        $this->view->assign('pager', $pages->getPager());
        return $this->view->fetch('user/listpages.tpl');
    }
    
    /**
     * the main user function
     *
     * @param array $args Arguments.
     *
     * @return string html string
     */
    public function mainAction($args)
    {
    
        if (!$this->getVar('enablecategorization')) {
            // list all pages
            return $this->listPages($args);
        } else {
            // show a list of the categories
            return $this->categories();
        }
    }
    
    /**
     * list all categories
     *
     * @return string html string
     */
    public function categoriesAction()
    {
    
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Pages::', '::', ACCESS_READ), LogUtil::getErrorMsgPermission());
        $this->view->setCacheId('main');
        if ($this->view->is_cached('user/main.tpl')) {
            return $this->view->fetch('user/main.tpl');
        }
        // get the categories registered for the Pages
        list($properties, $propertiesdata) = ModUtil::apiFunc($this->name, 'user', 'getCategories');
        // Assign some useful vars to customize the main
        $this->view->assign('properties', $properties);
        $this->view->assign('propertiesdata', $propertiesdata);
        return $this->view->fetch('user/main.tpl');
    }
    
    /**
     * view items
     *
     * @param array $args Arguments.
     *
     * @return string html string
     */
    public function viewAction($args)
    {
    
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Pages::', '::', ACCESS_OVERVIEW), LogUtil::getErrorMsgPermission());
        $lang = ZLanguage::getLanguageCode();
        $startnum = (int) FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 1, 'GET');
        $prop = (string) FormUtil::getPassedValue('prop', isset($args['prop']) ? $args['prop'] : null, 'GET');
        $cat = (string) FormUtil::getPassedValue('cat', isset($args['cat']) ? $args['cat'] : null, 'GET');
        $this->view->assign('startnum', $startnum);
        $itemsperpage = $this->getVar('itemsperpage');
        $this->view->assign('action', '');
        $category = CategoryUtil::getCategoryByID($cat);
        if (isset($category['display_name'][$lang])) {
            $this->view->assign('categoryname', $category['display_name'][$lang]);
        } else {
            $this->view->assign('categoryname', $category['name']);
        }
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')->from('Pages_Entity_Page', 'p')->join('p.categories', 'c')->where('c.category = :categories')->setParameter('categories', $cat);
        $pages = $qb->getQuery()->getArrayResult();
        $this->view->assign('pages', $pages);
        // assign the values for the smarty plugin to produce a pager
        $pager = array('numitems' => ModUtil::apiFunc('Pages', 'user', 'countitems', array('category' => $cat)), 'itemsperpage' => $itemsperpage);
        $this->view->assign('pager', $pager);
        // Return the output that has been generated by this function
        // is not practical to check for is_cached earlier in this method.
        $this->view->setCacheId('view|prop_' . $prop . '_cat_' . $cat . '|stnum_' . $startnum . '_' . $itemsperpage);
        return $this->view->fetch('user/view.tpl');
    }
    
    /**
     * display item
     *
     * @param $args array Arguments array.
     *
     * @return string html string
     */
    public function displayAction($args)
    {
    
        $pageid = FormUtil::getPassedValue('pageid', isset($args['pageid']) ? $args['pageid'] : null, 'REQUEST');
        $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null, 'REQUEST');
        $page = FormUtil::getPassedValue('page', isset($args['page']) ? $args['page'] : null, 'REQUEST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        if (!empty($objectid)) {
            $pageid = $objectid;
        }
        // Validate the essential parameters
        if ((empty($pageid) || !is_numeric($pageid)) && empty($title)) {
            return LogUtil::registerArgsError();
        }
        if (!empty($title)) {
            unset($pageid);
        }
        // Set the default page number
        if (empty($page) || $page < 1 || !is_numeric($page)) {
            $page = 1;
        }
        // Get the page
        $accesslevel = 0;
        if (isset($pageid)) {
            $item = new Pages_Access_Page($this->getEntityManager());
            $item->findById($pageid);
            $accesslevel = $item->getAccessLevel();
            $item = $item->get();
        } else {
            $params = array('title' => $title, 'catregistry' => isset($catregistry) ? $catregistry : null);
            $item = ModUtil::apiFunc('Pages', 'user', 'get', $params);
            System::queryStringSetVar('pageid', $item['pageid']);
            $pageid = $item['pageid'];
        }
        // Regardless of caching, we need to increment the read count and set the cache ID
        if (isset($pageid)) {
            $this->view->setCacheId($pageid . '|' . $page . '_a' . $accesslevel);
            $incrementresult = ModUtil::apiFunc('Pages', 'user', 'incrementreadcount', array('pageid' => $pageid));
        } else {
            $this->view->setCacheId($title . '|' . $page . '_a' . $accesslevel);
            $incrementresult = ModUtil::apiFunc('Pages', 'user', 'incrementreadcount', array('title' => $title));
        }
        if ($incrementresult === false) {
            return LogUtil::registerError($this->__('No such page found.'), 404);
        }
        // determine which template to render this page with
        // A specific template may exist for this page (based on page id)
        if (isset($pageid) && $this->view->template_exists('user/display_' . $pageid . '.tpl')) {
            $template = 'user/display_' . $pageid . '.tpl';
        } else {
            $template = 'user/display.tpl';
        }
        // check if the contents are cached.
        if ($this->view->is_cached($template)) {
            return $this->view->fetch($template);
        }
        // The return value of the function is checked here
        if ($item === false) {
            return LogUtil::registerError($this->__('No such page found.'), 404);
        }
        // Explode the page into an array of seperate pages based upon the pagebreak
        $allpages = explode('<!--pagebreak-->', $item['content']);
        // validates that the requested page exists
        if (!isset($allpages[$page - 1])) {
            return LogUtil::registerError($this->__('No such page found.'), 404);
        }
        // Set the item content to be the required page
        // nb arrays start from zero pages from one
        $item['content'] = trim($allpages[$page - 1]);
        $numitems = count($allpages);
        unset($allpages);
        // Display Admin Edit Link
        if ($accesslevel >= ACCESS_EDIT) {
            $this->view->assign('displayeditlink', true);
        } else {
            $this->view->assign('displayeditlink', false);
        }
        // Assign details of the item.
        $this->view->assign('item', $item);
        $this->view->assign('lang', ZLanguage::getLanguageCode());
        // Now lets assign the informatation to create a pager for the review
        $pager = array('numitems' => $numitems, 'itemsperpage' => 1);
        $this->view->assign('pager', $pager);
        return $this->view->fetch($template);
    }

}