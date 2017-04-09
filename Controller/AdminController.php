<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\PagesModule\Manager\PageCollectionManager;
use Zikula\Core\Controller\AbstractController;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Component\SortableColumns\Column;
use Zikula\PagesModule\AdminAuthInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class AdminController
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController implements AdminAuthInterface
{
    /**
     * @Route("")
     * @Theme("admin")
     *
     * view items
     *
     * @param Request $request
     *
     * @return Response HTML output
     */
    public function indexAction(Request $request)
    {
        // Get parameters
        $startnum = $request->query->get('startnum', 1);
        $orderBy = $request->query->get('orderby', 'pageid');
        $currentSortDirection = $request->query->get('sdir', Column::DIRECTION_DESCENDING);

        $filterForm = $this->createForm('Zikula\PagesModule\Form\Type\FilterType', [], [
            'translator' => $this->getTranslator(),
            'action' => $this->generateUrl('zikulapagesmodule_admin_index'),
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);
        $filterData = $filterForm->isSubmitted() ? $filterForm->getData() : $request->query->all();

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulapagesmodule_admin_index', 'orderby', 'sdir');
        $sortableColumns->addColumn(new Column('pageid')); // first added is automatically the default
        $sortableColumns->addColumn(new Column('title'));
        $sortableColumns->addColumn(new Column('cr_date'));
        $sortableColumns->setOrderBy($sortableColumns->getColumn($orderBy), $currentSortDirection);
        $sortableColumns->setAdditionalUrlParameters($request->query->all());

        $pages = new PageCollectionManager($this->get('doctrine')->getManager());
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage($this->getVar('itemsperpage'));
        $pages->setOrder($orderBy, $currentSortDirection);
        $pages->enablePager();
        $pages->setFilterBy($filterData);

        $templateParameters = [];
        $templateParameters['filter_active'] = !empty($filterData['categories']) || !empty($filterData['language']);
        $templateParameters['sort'] = $sortableColumns->generateSortableColumns();
        $templateParameters['pages'] = $pages->get();
        $templateParameters['lang'] = $request->getLocale();
        $templateParameters['pager'] = $pages->getPager();
        $templateParameters['modvars']['ZikulaPagesModule'] = $this->getVars(); // temporary solution
        $templateParameters['modvars']['ZConfig'] = $this->get('zikula_extensions_module.api.variable')->getAll('ZConfig'); // temporary solution
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
        $pages = $this->get('doctrine')->getManager()->getRepository('ZikulaPagesModule:PageEntity')->findAll();
        foreach ($pages as $page) {
            $page->setUrltitle(null); // reset the Gedmo/Sluggable field
        }
        $this->get('doctrine')->getManager()->flush();

        $this->addFlash('status', $this->__('Permalinks have been reset.'));

        $referer = $request->headers->get('referer');
        $url = strpos($referer, 'purge') ? $this->get('router')->generate('zikulapagesmodule_admin_index') : $referer;

        return new RedirectResponse($url);
    }
}
