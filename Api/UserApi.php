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

namespace Zikula\PagesModule\Api;

use Zikula\PagesModule\Manager\PageManager;
use Zikula\PagesModule\Manager\PageCollectionManager;
use LogUtil;
use ModUtil;
use System;
use FormUtil;
use CategoryUtil;
use Zikula_View;
use Zikula_View_Theme;
use CategoryRegistryUtil;

class UserApi extends \Zikula_AbstractApi
{

    /**
     * get a specific item
     *
     * @param $args['pageid'] id of example item to get
     *
     * @return mixed item array, or false on failure
     */
    public function get($args)
    {
        $page = new PageManager($this->getEntityManager());
        $page->find($args);
        return $page->toArray();
    }
    
    /**
     * get all pages
     *
     * @param array $args Arguments.
     *
     * @return mixed array of items, or false on failure
     */
    public function getall($args)
    {
        $pages = new PageCollectionManager();
        if (isset($args['startnum']) && !empty($args['category'])) {
            $pages->setStartNumber($args['startnum']);
        }
        if (isset($args['category']) && !empty($args['category'])) {
            $pages->setStartNumber($args['startnum']);
        }
        if (isset($args['language']) && !empty($args['language'])) {
            $pages->setLanguage($args['language']);
        }
        $orderby = 'pageid';
        if (isset($args['order']) && !empty($args['order'])) {
            $orderby = strtolower($args['order']);
        }
        $orderdir = 'DESC';
        if (isset($args['orderdir']) && !empty($args['orderdir'])) {
            $orderdir = $args['orderdir'];
        }
        $pages->setOrder($orderby, $orderdir);
        return $pages->get();
    }
    
    /**
     * utility function to count the number of items held by this module
     *
     * @param array $args Arguments.
     *
     * @return integer number of items held by this module
     */
    public function countitems($args)
    {
        if (isset($args['category']) && !empty($args['category'])) {
            if (is_array($args['category'])) {
                $args['category'] = $args['category']['Main'][0];
            }
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('count(p)')
                ->from('ZikulaPagesModule:PageEntity', 'p')
                ->join('p.categories', 'c')
                ->where('c.category = :categories')
                ->setParameter('categories', $args['category']);
            return $qb->getQuery()->getSingleScalarResult();
        }
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(p)')->from('ZikulaPagesModule:PageEntity', 'p');
        return $qb->getQuery()->getSingleScalarResult();
    }
    
    /**
     * increment the item read count
     *
     * @param array $args Arguments.
     *
     * @return bool true on success, false on failiure
     */
    public function incrementreadcount($args)
    {
        $page = new PageManager($this->getEntityManager());
        $page->find($args);
        return $page->incrementReadCount();
    }
    
    /**
     * form custom url string
     *
     * @param array $args Arguments array.
     *
     * @return string custom url string
     */
    public function encodeurl($args)
    {
        // check we have the required input
        if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
            return LogUtil::registerArgsError();
        }
        if (!isset($args['type'])) {
            $args['type'] = 'user';
        }
        // create an empty string ready for population
        $vars = '';
        // view function
        if ($args['func'] == 'view' && isset($args['args']['prop'])) {
            $vars = $args['args']['prop'];
            if (isset($args['args']['cat'])) {
                $vars .= '/' . $args['args']['cat'];
            }
            if (isset($args['args']['startnum']) && $args['args']['startnum'] != 1) {
                $vars .= (empty($vars) ? '' : '/') . 'startnum/' . $args['args']['startnum'];
            }
        }
        // for the display function use either the title (if present) or the page id
        if ($args['func'] == 'display') {
            // check for the generic object id parameter
            if (isset($args['args']['objectid'])) {
                $args['args']['pageid'] = $args['args']['objectid'];
            }
            // get the item (will be cached by doctrine)
            if (isset($args['args']['pageid'])) {
                $findBy = array('pageid' => $args['args']['pageid']);
            } else {
                $findBy = array('title' => $args['args']['title']);
            }
            $page = new PageManager($this->getEntityManager());
            $page->find($findBy);
            $item = $page->get();
            /**
             * @var \Zikula\PagesModule\Entity\CategoryEntity $category
             */
            $category = $page->get()->getCategories()->first();
            if (ModUtil::getVar($this->name, 'addcategorytitletopermalink') && $category) {
                $vars = $category->getCategory()->getName() . '/' . $item['urltitle'];
            } else {
                $vars = $item['urltitle'];
            }
            if (isset($args['args']['page']) && $args['args']['page'] != 1) {
                $vars .= '/page/' . $args['args']['page'];
            }
        }
        // construct the custom url part
        if (empty($args['func']) && empty($vars)) {
            return $args['modname'] . '/';
        } elseif (empty($args['func'])) {
            return $args['modname'] . '/' . $vars . '/';
        } elseif (empty($vars)) {
            return $args['modname'] . '/' . $args['func'] . '/';
        } else {
            return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
        }
    }
    
    /**
     * decode the custom url string
     *
     * @param array $args Arguments array.
     *
     * @return bool true if successful, false otherwise
     */
    public function decodeurl($args)
    {
        // check we actually have some vars to work with...
        if (!isset($args['vars'])) {
            return LogUtil::registerArgsError();
        }
        // define the available user functions
        $funcs = array('main', 'view', 'display');
        // set the correct function name based on our input
        if (empty($args['vars'][2])) {
            System::queryStringSetVar('func', 'main');
        } elseif (!in_array($args['vars'][2], $funcs)) {
            System::queryStringSetVar('func', 'display');
            $nextvar = 2;
        } else {
            System::queryStringSetVar('func', $args['vars'][2]);
            $nextvar = 3;
        }
        // add the category info
        if (FormUtil::getPassedValue('func') == 'view' && isset($args['vars'][$nextvar])) {
            // get rid of unused vars
            $args['vars'] = array_slice($args['vars'], $nextvar);
            System::queryStringSetVar('prop', (string) $args['vars'][0]);
            if (isset($args['vars'][1])) {
                // check if there's a page arg
                $varscount = count($args['vars']);
                $args['vars'][$varscount - 2] == 'startnum' ? $pagersize = 2 : ($pagersize = 0);
                System::queryStringSetVar('startnum', $args['vars'][$varscount - 1]);
                // extract the category path
                $cat = implode('/', array_slice($args['vars'], 1, $varscount - $pagersize - 1));
                $category = CategoryUtil::getCategoryByPath($cat, 'name');
                System::queryStringSetVar('cat', $category['id']);
            }
        }
        // identify the correct parameter to identify the page
        if (FormUtil::getPassedValue('func') == 'display') {
            // get rid of unused vars
            $args['vars'] = array_slice($args['vars'], $nextvar);
            $nextvar = 0;
            // remove any category path down to the leaf category
            $varscount = count($args['vars']);
            if (ModUtil::getVar($this->name, 'addcategorytitletopermalink') && count($args['vars']) == 4 || count($args['vars']) == 2) {
                $args['vars'][$varscount - 2] == 'page' ? $pagersize = 2 : ($pagersize = 0);
                $cat = array_slice($args['vars'], 0, $varscount - 1 - $pagersize);
                $category = CategoryUtil::getCategoryByPath($cat, 'name');
                System::queryStringSetVar('cat', $category['id']);
                array_splice($args['vars'], 0, $varscount - 1 - $pagersize);
            }
            if (is_numeric($args['vars'][$nextvar])) {
                System::queryStringSetVar('pageid', $args['vars'][$nextvar]);
            } else {
                System::queryStringSetVar('title', $args['vars'][$nextvar]);
            }
            $nextvar++;
            if (isset($args['vars'][$nextvar]) && $args['vars'][$nextvar] == 'page') {
                System::queryStringSetVar('page', (int) $args['vars'][$nextvar + 1]);
            }
        }
        return true;
    }
    
    /**
     * get meta data for the module
     *
     * @return array
     */
    public function getmodulemeta()
    {
        return array('viewfunc' => 'view', 'displayfunc' => 'display', 'newfunc' => 'new', 'createfunc' => 'create', 'modifyfunc' => 'modify', 'updatefunc' => 'update', 'deletefunc' => 'delete', 'titlefield' => 'title', 'itemid' => 'pageid');
    }
    
    /**
     * Clear cache for given item. Can be called from other modules to clear an item cache.
     *
     * @param array $item array with data or id of the item
     */
    public function clearItemCache($item)
    {
        if ($item && !is_array($item)) {
            $item = ModUtil::apiFunc($this->name, 'user', 'get', array('sid' => $item));
        }
        if ($item) {
            // Clear View_cache
            $cacheIds = array();
            $cacheIds[] = $item['sid'];
            $cacheIds[] = 'view';
            $cacheIds[] = 'main';
            $view = Zikula_View::getInstance($this->name);
            foreach ($cacheIds as $cacheId) {
                $view->clear_cache(null, $cacheId);
            }
            // Clear Theme_cache
            $cacheIds = array();
            // for given page Id, according to new cacheId structure in Zikula 1.3.2.dev (1.3.3)
            $cacheIds[] = 'Pages/user/display/pageid_' . $item['pageid'];
            $cacheIds[] = 'homepage';
            // for homepage (it can be adjustment in module settings)
            $cacheIds[] = 'Pages/user/view';
            // view function (pages list)
            $cacheIds[] = 'Pages/user/main';
            // main function
            $theme = Zikula_View_Theme::getInstance();
            //if (Zikula_Core::VERSION_NUM > '1.3.2') {
            if (method_exists($theme, 'clear_cacheid_allthemes')) {
                $theme->clear_cacheid_allthemes($cacheIds);
            } else {
                // clear cache for current theme only
                foreach ($cacheIds as $cacheId) {
                    $theme->clear_cache(null, $cacheId);
                }
            }
        }
    }
    
    /**
     * Get the categories registered for the Pages
     *
     * @return array
     */
    public function getCategories()
    {
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'Page');
        $properties = array_keys($catregistry);
        $propertiesdata = array();
        foreach ($properties as $property) {
            $rootcat = CategoryUtil::getCategoryByID($catregistry[$property]);
            if (!empty($rootcat)) {
                $rootcat['path'] .= '/';
                // add this to make the relative paths of the subcategories with ease - mateo
                $subcategories = CategoryUtil::getCategoriesByParentID($rootcat['id']);
                foreach ($subcategories as $k => $category) {
                    $subcategories[$k]['count'] = ModUtil::apiFunc($this->name, 'user', 'countitems', array('category' => $category['id'], 'property' => $property));
                }
                $propertiesdata[] = array('name' => $property, 'rootcat' => $rootcat, 'subcategories' => $subcategories);
            }
        }
        return array($properties, $propertiesdata);
    }

}