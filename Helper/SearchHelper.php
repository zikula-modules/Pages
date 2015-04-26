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

namespace Zikula\PagesModule\Helper;

use Zikula\Module\SearchModule\AbstractSearchable;
use SecurityUtil;
use ModUtil;
use Zikula\Core\RouteUrl;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param boolean $active if the module should be checked as active
     * @param array|null $modVars module form vars as previously set
     * @return string
     */
    public function getOptions($active, $modVars = null)
    {
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $render = \Zikula_View::getInstance($this->name);
            $render->assign('active', $active);

            return $render->fetch('Search/options.tpl');
        }

        return '';
    }

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            return array();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')->from('Zikula\PagesModule\Entity\PageEntity', 'p');
        $whereExpr = $this->formatWhere($qb, $words, array('t.title', 'p.content'), $searchType);
        $qb->andWhere($whereExpr);
        $pages = $qb->getQuery()->getResult();

        $sessionId = session_id();
        $addCategoryTitleToPermalink = ModUtil::getVar($this->name, 'addcategorytitletopermalink');
        $enableCategorization = ModUtil::getVar($this->name, 'enablecategorization');

        $records = array();
        foreach ($pages as $page) {
            /** @var $page \Zikula\PagesModule\Entity\PageEntity */

            $pagePermissionCheck = SecurityUtil::checkPermission($this->name . '::', $page->getTitle() .'::' . $page->getPageid(), ACCESS_OVERVIEW);
            if ($enableCategorization) {
                $pagePermissionCheck = $pagePermissionCheck && \CategoryUtil::hasCategoryAccess($page->getCategories(), $this->name);
            }
            if (!$pagePermissionCheck) {
                continue;
            }

            if ($addCategoryTitleToPermalink) {
                $cat = isset($obj['__CATEGORIES__']['Main']['name']) ? $obj['__CATEGORIES__']['Main']['name'] : null;
                $params = array('pageid' => $page->getPageid(), 'cat' => $cat);
            } else {
                $params = array('pageid' => $page->getPageid());
            }

            $records[] = array(
                'title' => $page->getTitle(),
                'text' => $page->getContent(),
                'created' => $page->getCr_date(),
                'module' => $this->name,
                'sesid' => $sessionId,
                'url' => RouteUrl::createFromRoute('zikulapagesmodule_user_display', $params)
            );
        }

        return $records;
    }

}