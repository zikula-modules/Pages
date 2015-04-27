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
use ModUtil;
use CategoryUtil;
use Zikula_View;
use Zikula_View_Theme;
use CategoryRegistryUtil;

/**
 * Class UserApi
 * @package Zikula\PagesModule\Api
 */
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
     * get meta data for the module
     *
     * @return array
     */
    public function getmodulemeta()
    {
        return array(
            'viewfunc' => 'view',
            'displayfunc' => 'display',
            'newfunc' => 'new',
            'createfunc' => 'create',
            'modifyfunc' => 'modify',
            'updatefunc' => 'update',
            'deletefunc' => 'delete',
            'titlefield' => 'title',
            'itemid' => 'pageid'
        );
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
            $cacheIds[] = 'Pages/User/display/pageid_' . $item['pageid'];
            $cacheIds[] = 'homepage';
            // for homepage (it can be adjustment in module settings)
            $cacheIds[] = 'Pages/User/view';
            // view function (pages list)
            $cacheIds[] = 'Pages/User/main';
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