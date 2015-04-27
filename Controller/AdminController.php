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

use CategoryRegistryUtil;
use FormUtil;
use ModUtil;
use SecurityUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PagesModule\Manager\PageCollectionManager;
use Zikula\PagesModule\Util as PagesUtil;
use Zikula_View;
use ZLanguage;

/**
 * Class AdminController
 * @package Zikula\PagesModule\Controller
 *
 * @Route("/admin")
 */
class AdminController extends \Zikula_AbstractController
{
    public function postInitializeAction()
    {
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * @Route("")
     *
     * the main administration function
     *
     * @param Request $request
     *
     * @return RedirectResponse HTML output
     */
    public function indexAction(Request $request)
    {
        return new RedirectResponse($this->get('router')->generate('zikulapagesmodule_admin_view'));
    }

    /**
     * @Route("/modify")
     *
     * modify a page
     *
     * @param Request $request
     *
     * @return Response HTML output
     */
    public function modifyAction(Request $request)
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('Admin/modify.tpl', new \Zikula\PagesModule\Handler\ModifyHandler()));
    }

    /**
     * @Route("/delete")
     *
     * delete item
     *
     * @param Request $request
     *
     * @return Response HTML output
     */
    public function deleteAction(Request $request)
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('Admin/delete.tpl', new \Zikula\PagesModule\Handler\DeleteHandler()));
    }

    /**
     * @Route("/view")
     *
     * view items
     *
     * @param Request $request
     *
     * @return Response HTML output
     */
    public function viewAction(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        // initialize sort array - used to display sort classes and urls
        $sort = array();
        $fields = array('pageid', 'title', 'cr_date');
        // possible sort fields
        foreach ($fields as $field) {
            $sort['class'][$field] = 'z-order-unsorted';
        }
        // Get parameters from whatever input we need.
        $startnum = $request->get('startnum', 1);
        $language = $request->request->get('language', null);
        $orderby = $request->get('orderby', 'pageid');
        $originalSdir = $request->get('sdir', 'ASC');

        $this->view->assign('startnum', $startnum);
        $this->view->assign('orderby', $orderby);
        $this->view->assign('sdir', $originalSdir);
        $sdir = $originalSdir ? 0 : 1;
        $sort['class'][$orderby] = ($sdir == 0) ? 'z-order-desc' : 'z-order-asc';
        $orderdir = ($sdir == 0) ? 'DESC' : 'ASC';

        $filtercats = $request->get('pages', null);
        $filtercatsSerialized = $request->query->get('filtercats_serialized', false);
        $filtercats = $filtercatsSerialized ? unserialize($filtercatsSerialized) : $filtercats;
        $catsarray = PagesUtil::formatCategoryFilter($filtercats);
        // complete initialization of sort array, adding urls
        foreach ($fields as $field) {
            $params = array('language' => $language, 'filtercats_serialized' => serialize($filtercats), 'orderby' => $field, 'sdir' => $sdir);
            $sort['url'][$field] = $this->get('router')->generate('zikulapagesmodule_admin_view', $params);
        }
        $this->view->assign('sort', $sort);
        $this->view->assign('filter_active', empty($language) && empty($catsarray) ? false : true);
        // get module vars
        $modvars = $this->getVars();
        if ($modvars['enablecategorization']) {
            $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'Page');
            $this->view->assign('catregistry', $catregistry);
        }
        $pages = new PageCollectionManager();
        $pages->setStartNumber($startnum);
        $pages->setLanguage($language);
        $pages->setOrder($orderby, $orderdir);
        $pages->enablePager();
        if (isset($catsarray['Main'])) {
            $pages->setCategory($catsarray['Main']);
        }
        // Assign the items to the template
        $this->view->assign('pages', $pages->get());
        // Assign the default language
        $this->view->assign('lang', ZLanguage::getLanguageCode());
        $this->view->assign('language', $language);
        // Assign the information required to create the pager
        $this->view->assign('pager', $pages->getPager());
        $selectedcategories = array();
        if (is_array($filtercats)) {
            $catsarray = $filtercats['__CATEGORIES__'];
            foreach ($catsarray as $propname => $propid) {
                if ($propid > 0) {
                    $selectedcategories[$propname] = $propid;
                }
            }
        }
        $this->view->assign('selectedcategories', $selectedcategories);

        return new Response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * @Route("/purge")
     *
     * purge permalinks
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function purgeAction(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (ModUtil::apiFunc($this->name, 'admin', 'purgepermalinks')) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Purging of the pemalinks was successful'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Purging of the pemalinks has failed'));
        }
        $referer = $request->headers->get('referer');
        $url = strpos($referer, 'purge') ? $this->get('router')->generate('zikulapagesmodule_admin_view') : $referer;

        return new RedirectResponse($url);
    }

    /**
     * @Route("/config")
     *
     * modify module configuration
     *
     * @param Request $request
     *
     * @return Response HTML output string
     */
    public function modifyconfigAction(Request $request)
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('Admin/modifyconfig.tpl', new \Zikula\PagesModule\Handler\ModifyConfigHandler()));
    }

}