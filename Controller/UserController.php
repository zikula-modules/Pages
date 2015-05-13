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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Manager\PageCollectionManager;
use ZLanguage;
use Zikula\Core\Controller\AbstractController;

/**
 * Class UserController
 * @package Zikula\PagesModule\Controller
 */
class UserController extends AbstractController
{
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
        if (!\ModUtil::getVar($this->name, 'enablecategorization')) {
            // list all pages
            return new RedirectResponse($this->get('router')->generate('zikulapagesmodule_user_listpages'));
        } else {
            // show a list of the categories
            return new RedirectResponse($this->get('router')->generate('zikulapagesmodule_user_categories'));
        }
    }

    /**
     * @Route("/list/{startnum}", requirements={"startnum" = "^[1-9]\d*$"})
     *
     * list all pages
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response html string
     */
    public function listPagesAction(Request $request, $startnum = 1)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $pages = new PageCollectionManager($this->container->get('doctrine.entitymanager'));
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage(\ModUtil::getVar($this->name, 'itemsperpage'));
        $pages->setOrder('title', 'ASC');
        $pages->enablePager();

        $templateParameters['pages'] = $pages->get();
        $templateParameters['pager'] = $pages->getPager();

        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render('ZikulaPagesModule:User:listpages.html.twig', $templateParameters);
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

        // get the registered categories
        list($properties, $propertiesdata) = ModUtil::apiFunc($this->name, 'user', 'getCategories');

        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render('ZikulaPagesModule:User:main.html.twig', array('properties' => $properties, 'propertiesdata' => $propertiesdata, 'lang' => \ZLanguage::getLanguageCode()));
    }

    /**
     * @Route("/view/{prop}/{cat}/{startnum}", requirements={"startnum" = "^[1-9]\d*$", "cat" = "^[1-9]\d*$"})
     *
     * view page list
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response html string
     */
    public function viewAction(Request $request, $prop, $cat, $startnum = 1)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        // @TODO the prop category must be converted to the propId and links adjusted throughout
        // then the CollectionManager must also include this parameter in the search

        $pages = new PageCollectionManager($this->container->get('doctrine.entitymanager'));
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage(\ModUtil::getVar($this->name, 'itemsperpage'));
        $pages->setOrder('title', 'ASC');
        $pages->setCategory($cat);
        $pages->enablePager();

        $templateParameters = array(
            'startnum' => $startnum,
            'category' => CategoryUtil::getCategoryByID($cat),
            'lang' => \ZLanguage::getLanguageCode(),
            'pages' => $pages->get(),
            'pager' => $pages->getPager()
        );
        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render('ZikulaPagesModule:User:view.html.twig', $templateParameters);
    }

    /**
     * @Route("/display/{urltitle}/{pagenum}", requirements={"pagenum" = "^[1-9]\d*$"})
     *
     * display page
     *
     * @param Request $request
     * @param PageEntity $page
     * @param int $pagenum
     * @return Response
     */
    public function displayAction(Request $request, PageEntity $page, $pagenum = 1)
    {
        $accessLevel = $this->getAccessLevel($page);
        if ($accessLevel == ACCESS_NONE) {
            throw new AccessDeniedHttpException();
        }

        $page->incrementCounter();
        $this->get('doctrine.entitymanager')->flush($page);

        // A custom template may exist for this page (based on page id)
        $customTemplateName = 'ZikulaPagesModule:User:display_' . $page->getPageid() . '.html.twig';
        $templateName = ($this->get('templating')->exists($customTemplateName)) ? $customTemplateName : 'ZikulaPagesModule:User:display.html.twig';

        // Explode the page into an array of separate pages based upon the pagebreak
        $allPages = explode('<!--pagebreak-->', $page->getContent());
        // validates that the requested page exists
        if (!isset($allPages[$pagenum - 1])) {
            throw new NotFoundHttpException(__('No such page found.'));
        }
        // Set the item content to be the required page
        // arrays are zero-based
        $page->setContent(trim($allPages[$pagenum - 1]));
        $numitems = count($allPages);
        unset($allPages);
        $templateParameters = array();
        $templateParameters['displayeditlink'] = ($accessLevel >= ACCESS_EDIT);
        $templateParameters['page'] = $page;
        $templateParameters['lang'] = ZLanguage::getLanguageCode();
        $templateParameters['modvars'] = \ModUtil::getModvars(); // @todo temporary solution
        $templateParameters['pager'] = array('numitems' => $numitems, 'itemsperpage' => 1);

        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render($templateName, $templateParameters);
    }

    /**
     * Find Access level for current user for this page
     *
     * @return integer (ACCESS CONSTANT)
     */
    private function getAccessLevel(PageEntity $page)
    {
        if (SecurityUtil::checkPermission('ZikulaPagesModule::Page', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_READ)) {
            $accessLevel = ACCESS_READ;
            if (SecurityUtil::checkPermission('ZikulaPagesModule::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_COMMENT)) {
                $accessLevel = ACCESS_COMMENT;
                if (SecurityUtil::checkPermission('ZikulaPagesModule::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_EDIT)) {
                    $accessLevel = ACCESS_EDIT;
                }
            }
        } else {
            $accessLevel = ACCESS_NONE;
        }

        return $accessLevel;
    }

}