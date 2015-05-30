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

use CategoryRegistryUtil;
use CategoryUtil;

/**
 * Class UserApi
 * @package Zikula\PagesModule\Api
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * utility function to count the number of items held by this module
     *
     * @param array $args Arguments.
     *
     * @return integer number of items held by this module
     */
    private function countItems($args)
    {
        if (isset($args['category']) && !empty($args['category'])) {
            if (is_array($args['category'])) {
                $args['category'] = $args['category']['Main'][0];
            }
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('count(p)')
                ->from('Zikula\PagesModule\Entity\PageEntity', 'p')
                ->join('p.categories', 'c')
                ->where('c.category = :categories')
                ->setParameter('categories', $args['category']);

            return $qb->getQuery()->getSingleScalarResult();
        }
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(p)')->from('Zikula\PagesModule\Entity\PageEntity', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get the categories registered for the Pages
     *
     * @return array
     */
    public function getCategories()
    {
        $categoryRegistry = CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'PageEntity');
        $properties = array_keys($categoryRegistry);
        $propertiesdata = array();
        foreach ($properties as $property) {
            $rootcat = CategoryUtil::getCategoryByID($categoryRegistry[$property]);
            if (!empty($rootcat)) {
                $rootcat['path'] .= '/';
                // add this to make the relative paths of the subcategories with ease - mateo
                $subcategories = CategoryUtil::getCategoriesByParentID($rootcat['id']);
                foreach ($subcategories as $k => $category) {
                    $subcategories[$k]['count'] = $this->countItems(array('category' => $category['id'], 'property' => $property));
                }
                $propertiesdata[] = array('name' => $property, 'rootcat' => $rootcat, 'subcategories' => $subcategories);
            }
        }

        return array($properties, $propertiesdata);
    }

}