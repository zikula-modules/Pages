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
use ModUtil;
use SecurityUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PagesModule\Manager\PageCollectionManager;
use ZLanguage;
use Zikula\Core\Controller\AbstractController;
use Zikula\PagesModule\Form\Type\FilterType;

/**
 * Class AdminController
 * @package Zikula\PagesModule\Controller
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    const SORTDIR_ASC = 0;
    const SORTDIR_DESC = 1;
    /**
     * @Route("")
     *
     * view items
     *
     * @param Request $request
     *
     * @return Response HTML output
     */
    public function indexAction(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // initialize sort array - used to display sort classes and urls
        $sort = array();
        $sortableFields = array('pageid', 'title', 'cr_date');
        // possible sort fields
        foreach ($sortableFields as $field) {
            $sort['class'][$field] = 'z-order-unsorted';
        }
        // Get parameters
        $startnum = $request->query->get('startnum', 1);
        $language = $request->query->get('language', null);
        $orderby = $request->query->get('orderby', 'pageid');
        $currentSortDirection = $request->query->get('sdir', self::SORTDIR_DESC);

        $templateParameters = array(
            'startnum' => $startnum,
            'orderby' => $orderby,
            'sdir' => $currentSortDirection,
        );
        $possibleSortDirection = ($currentSortDirection == self::SORTDIR_DESC) ? self::SORTDIR_ASC : self::SORTDIR_DESC;
        $sort['class'][$orderby] = ($possibleSortDirection == self::SORTDIR_ASC) ? 'z-order-desc' : 'z-order-asc';
        $orderdir = ($possibleSortDirection == self::SORTDIR_ASC) ? 'DESC' : 'ASC';

        $filterForm = $this->createForm(new FilterType(), $request->query->all(), array(
            'action' => $this->generateUrl('zikulapagesmodule_admin_index'),
            'method' => 'POST',
            'entityCategoryRegistries' => CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'Page', 'id'),
        ));
        $filterForm->handleRequest($request);
        $filterData = $filterForm->isSubmitted() ? $filterForm->getData() : $request->query->all();
        $templateParameters['filter_active'] = (isset($filterData['category']) && (count($filterData['category']) > 0)) || !empty($filterData['language']);

        // complete initialization of sort array, adding urls
        foreach ($sortableFields as $field) {
            $params = array(
                'language' => isset($filterData['language']) ? $filterData['language'] : null,
                'filtercats_serialized' => isset($filterData['categories']) ? serialize($filterData['categories']) : null,
                'orderby' => $field,
                'sdir' => $possibleSortDirection);
            $sort['url'][$field] = $this->get('router')->generate('zikulapagesmodule_admin_index', $params);
        }
        $templateParameters['sort'] = $sort;

        $pages = new PageCollectionManager($this->container->get('doctrine.entitymanager'));
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage(\ModUtil::getVar($this->name, 'itemsperpage'));
        $pages->setOrder($orderby, $orderdir);
        $pages->enablePager();
        $pages->setFilterBy($filterData);
        // Assign the items to the template
        $templateParameters['pages'] = $pages->get();
        $templateParameters['lang'] = ZLanguage::getLanguageCode();
        // Assign the information required to create the pager
        $templateParameters['pager'] = $pages->getPager();
        $templateParameters['modvars'] = \ModUtil::getModvars(); // temporary solution
        $templateParameters['filterForm'] = $filterForm->createView();

        // attempt to disable caching for this only
        $response = new Response();
        $response->expire();

        return $this->render('ZikulaPagesModule:Admin:view.html.twig', $templateParameters, $response);
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
        $pages = $this->container->get('doctrine.entitymanager')->getRepository('ZikulaPagesModule:PageEntity')->findAll();
        foreach ($pages as $page) {
            $page->setUrltitle(null); // reset the Gedmo/Sluggable field
        }
        $this->container->get('doctrine.entitymanager')->flush();

        $this->addFlash('status', __('Permalinks have been reset.'));

        $referer = $request->headers->get('referer');
        $url = strpos($referer, 'purge') ? $this->get('router')->generate('zikulapagesmodule_admin_index') : $referer;

        return new RedirectResponse($url);
    }

}