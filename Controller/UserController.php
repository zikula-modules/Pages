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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Manager\PageCollectionManager;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class UserController
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
        if (!$this->getVar('enablecategorization')) {
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
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $pages = new PageCollectionManager($this->get('doctrine')->getManager());
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage($this->getVar('itemsperpage'));
        $pages->setOrder('title', 'ASC');
        $pages->enablePager();

        $templateParameters['pages'] = $pages->get();
        $templateParameters['pager'] = $pages->getPager();

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
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        /** @var CategoryRegistryEntity[] $registries */
        $registries = $this->get('zikula_categories_module.category_registry_repository')->findBy([
            'modname' => $this->getName(),
            'entityname' => 'PageEntity'
        ]);
        $count = [];
        $categoryAssignments = $this->get('doctrine')->getRepository('ZikulaPagesModule:CategoryAssignmentEntity')->findAll();
        foreach ($categoryAssignments as $assignment) {
            $id = $assignment->getCategory()->getId();
            $count[$id] = !isset($count[$id]) ? $count[$id] = 1 : $count[$id] + 1;
        }

        return $this->render('ZikulaPagesModule:User:categories.html.twig', [
            'registries' => $registries,
            'count' => $count
            ]);
    }

    /**
     * @Route("/view/{category}/{startnum}", requirements={"startnum" = "^[1-9]\d*$", "category" = "^[1-9]\d*$"})
     *
     * view page list
     *
     * @param Request $request
     *
     * @param CategoryEntity $category
     * @param int $startnum
     * @return Response
     */
    public function viewCategoryAction(Request $request, CategoryEntity $category = null, $startnum = 1)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        // @TODO the prop category must be converted to the propId and links adjusted throughout
        // then the CollectionManager must also include this parameter in the search

        $pages = new PageCollectionManager($this->get('doctrine')->getManager());
        $pages->setStartNumber($startnum);
        $pages->setItemsPerPage($this->getVar('itemsperpage'));
        $pages->setOrder('title', 'ASC');
        $pages->setCategory($category);
        $pages->enablePager();

        $templateParameters = [
            'startnum' => $startnum,
            'category' => $category,
            'lang' => $request->getLocale(),
            'pages' => $pages->get(),
            'pager' => $pages->getPager()
        ];

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
        $this->get('doctrine')->getManager()->flush($page);

        // A custom template may exist for this page (based on page id)
        $customTemplateName = 'ZikulaPagesModule:User:display_' . $page->getPageid() . '.html.twig';
        $templateName = ($this->get('templating')->exists($customTemplateName)) ? $customTemplateName : 'ZikulaPagesModule:User:display.html.twig';

        // Explode the page into an array of separate pages based upon the pagebreak
        $allPages = explode('<!--pagebreak-->', $page->getContent());
        // validates that the requested page exists
        if (!isset($allPages[$pagenum - 1])) {
            throw new NotFoundHttpException($this->__('No such page found.'));
        }
        // Set the item content to be the required page
        // arrays are zero-based
        $page->setContent(trim($allPages[$pagenum - 1]));
        $numitems = count($allPages);
        unset($allPages);
        $templateParameters = [];
        $templateParameters['displayeditlink'] = ($accessLevel >= ACCESS_EDIT);
        $templateParameters['page'] = $page;
        $templateParameters['lang'] = $request->getLocale();
        $templateParameters['modvars']['ZikulaPagesModule'] = $this->getVars(); // @todo temporary solution
        $templateParameters['pager'] = ['numitems' => $numitems, 'itemsperpage' => 1];

        return $this->render($templateName, $templateParameters);
    }

    /**
     * @Route("/print/{urltitle}")
     * @Theme("print")
     * display printable page
     *
     * @param Request $request
     * @param PageEntity $page
     * @return Response
     */
    public function displayPrintableAction(Request $request, PageEntity $page)
    {
        return $this->displayAction($request, $page);
    }

    /**
     * Find Access level for current user for this page
     *
     * @return integer (ACCESS CONSTANT)
     */
    private function getAccessLevel(PageEntity $page)
    {
        if ($this->hasPermission('ZikulaPagesModule::Page', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_READ)) {
            $accessLevel = ACCESS_READ;
            if ($this->hasPermission('ZikulaPagesModule::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_COMMENT)) {
                $accessLevel = ACCESS_COMMENT;
                if ($this->hasPermission('ZikulaPagesModule::', "{$page->getTitle()}::{$page->getPageid()}", ACCESS_EDIT)) {
                    $accessLevel = ACCESS_EDIT;
                }
            }
        } else {
            $accessLevel = ACCESS_NONE;
        }

        return $accessLevel;
    }
}
