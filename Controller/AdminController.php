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
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Component\SortableColumns\Column;

/**
 * Class AdminController
 * @package Zikula\PagesModule\Controller
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
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

        // Get parameters
        $startnum = $request->query->get('startnum', 1);
        $orderBy = $request->query->get('orderby', 'pageid');
        $currentSortDirection = $request->query->get('sdir', Column::DIRECTION_DESCENDING);

        $filterForm = $this->createForm(new FilterType(), $request->query->all(), array(
            'action' => $this->generateUrl('zikulapagesmodule_admin_index'),
            'method' => 'POST',
            'entityCategoryRegistries' => CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'Page', 'id'),
        ));
        $filterForm->handleRequest($request);
        $filterData = $filterForm->isSubmitted() ? $filterForm->getData() : $request->query->all();

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulapagesmodule_admin_index', 'orderby', 'sdir');
        $sortableColumns->addColumn(new Column('pageid')); // first added is automatically the default
        $sortableColumns->addColumn(new Column('title'));
        $sortableColumns->addColumn(new Column('cr_date'));
        $sortableColumns->setOrderBy($sortableColumns->getColumn($orderBy), $currentSortDirection);
        $sortableColumns->setAdditionalUrlParameters(array(
            'language' => isset($filterData['language']) ? $filterData['language'] : null,
            // @todo serialized cats not working yet
            'filtercats_serialized' => isset($filterData['categories']) ? serialize($filterData['categories']) : null,
        ));

        $pages = new PageCollectionManager($this->container->get('doctrine.entitymanager'));
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage(\ModUtil::getVar($this->name, 'itemsperpage'));
        $pages->setOrder($orderBy, $currentSortDirection);
        $pages->enablePager();
        $pages->setFilterBy($filterData);

        $templateParameters = array();
        $templateParameters['filter_active'] = (isset($filterData['category']) && (count($filterData['category']) > 0)) || !empty($filterData['language']);
        $templateParameters['sort'] = $sortableColumns->generateSortableColumns();
        $templateParameters['pages'] = $pages->get();
        $templateParameters['lang'] = ZLanguage::getLanguageCode();
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