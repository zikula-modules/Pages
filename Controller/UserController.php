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

namespace Zikula\PagesModule\Controller;

use CategoryUtil;
use ModUtil;
use SecurityUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use Zikula\PagesModule\Manager\PageCollectionManager;
use Zikula\PagesModule\Manager\PageManager;
use ZLanguage;

/**
 * Class UserController
 * @package Zikula\PagesModule\Controller
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("/list")
     *
     * list all pages
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response html string
     */
    public function listPagesAction(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $startnum = $request->query->get('startnum', 1);
        $this->view->assign('startnum', $startnum);
        $pages = new PageCollectionManager();
        $pages->setStartNumber($startnum);
        $pages->setOrder('title');
        $pages->enablePager();
        $this->view->assign('pages', $pages->get());
        // assign the values for the smarty plugin to produce a pager
        $this->view->assign('pager', $pages->getPager());

        return new Response($this->view->fetch('User/listpages.tpl'));
    }

    /**
     * @Route("")
     *
     * the index user function
     *
     * @param Request $request
     *
     * @return RedirectResponse html string
     */
    public function indexAction(Request $request)
    {
        if (!$this->getVar('enablecategorization')) {
            // list all pages
            return new RedirectResponse($this->get('router')->generate('zikulapagesmodule_user_listpages'));
        } else {
            // show a list of the categories
            return new RedirectResponse($this->get('router')->generate('zikulapagesmodule_user_categories'));
        }
    }

    /**
     * @Route("/categories")
     *
     * list all categories of pages
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response html string
     */
    public function categoriesAction(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $this->view->setCacheId('main');
        if ($this->view->is_cached('User/main.tpl')) {
            return new Response($this->view->fetch('User/main.tpl'));
        }
        // get the categories registered for the Pages
        list($properties, $propertiesdata) = ModUtil::apiFunc($this->name, 'user', 'getCategories');
        // Assign some useful vars to customize the main
        $this->view->assign('properties', $properties);
        $this->view->assign('propertiesdata', $propertiesdata);

        return new Response($this->view->fetch('User/main.tpl'));
    }

    /**
     * @Route("/view")
     *
     * view page list
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response html string
     */
    public function viewAction(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        $lang = ZLanguage::getLanguageCode();
        $startnum = $request->query->get('startnum', 1);
        $prop = $request->query->get('prop', null);
        $cat = $request->query->get('cat', null);
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
        $qb->select('p')
            ->from('ZikulaPagesModule:PageEntity', 'p')
            ->join('p.categories', 'c')
            ->where('c.category = :categories')
            ->setParameter('categories', $cat);
        $pages = $qb->getQuery()->getArrayResult();
        $this->view->assign('pages', $pages);
        // assign the values for the smarty plugin to produce a pager
        $pager = array('numitems' => ModUtil::apiFunc($this->name, 'user', 'countitems', array('category' => $cat)), 'itemsperpage' => $itemsperpage);
        $this->view->assign('pager', $pager);
        // Return the output that has been generated by this function
        // is not practical to check for is_cached earlier in this method.
        $this->view->setCacheId('view|prop_' . $prop . '_cat_' . $cat . '|stnum_' . $startnum . '_' . $itemsperpage);

        return new Response($this->view->fetch('User/view.tpl'));
    }

    /**
     * @Route("/display")
     *
     * display page
     *
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @throws NotFoundHttpException
     *
     * @return Response html string
     */
    public function displayAction(Request $request)
    {
        $pageid = $request->get('pageid', null);
        $title = $request->get('title', null);
        $page = $request->get('page', null);
        $objectid = $request->get('objectid', null);

        if (!empty($objectid)) {
            $pageid = $objectid;
        }
        // Validate the essential parameters
        if ((empty($pageid) || !is_numeric($pageid)) && empty($title)) {
            throw new \InvalidArgumentException();
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
            $item = new PageManager($this->getEntityManager());
            $item->findById($pageid);
            $accesslevel = $item->getAccessLevel();
            $item = $item->get();
        } else {
            $params = array('title' => $title, 'catregistry' => isset($catregistry) ? $catregistry : null);
            $item = ModUtil::apiFunc($this->name, 'user', 'get', $params);
            System::queryStringSetVar('pageid', $item['pageid']);
            $pageid = $item['pageid'];
        }
        // Regardless of caching, we need to increment the read count and set the cache ID
        if (isset($pageid)) {
            $this->view->setCacheId($pageid . '|' . $page . '_a' . $accesslevel);
            $incrementresult = ModUtil::apiFunc($this->name, 'user', 'incrementreadcount', array('pageid' => $pageid));
        } else {
            $this->view->setCacheId($title . '|' . $page . '_a' . $accesslevel);
            $incrementresult = ModUtil::apiFunc($this->name, 'user', 'incrementreadcount', array('title' => $title));
        }
        if ($incrementresult === false) {
            throw new NotFoundHttpException($this->__('No such page found.'));
        }
        // determine which template to render this page with
        // A specific template may exist for this page (based on page id)
        if (isset($pageid) && $this->view->template_exists('User/display_' . $pageid . '.tpl')) {
            $template = 'User/display_' . $pageid . '.tpl';
        } else {
            $template = 'User/display.tpl';
        }
        // check if the contents are cached.
        if ($this->view->is_cached($template)) {
            return new Response($this->view->fetch($template));
        }
        // The return value of the function is checked here
        if ($item === false) {
            throw new NotFoundHttpException($this->__('No such page found.'));
        }
        // Explode the page into an array of separate pages based upon the pagebreak
        $allpages = explode('<!--pagebreak-->', $item['content']);
        // validates that the requested page exists
        if (!isset($allpages[$page - 1])) {
            throw new NotFoundHttpException($this->__('No such page found.'));
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
        return new Response($this->view->fetch($template));
    }

}