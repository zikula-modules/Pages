<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Zikula\PagesModule\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManager;
use System;

class PageCollectionManager
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;
    /**
     * @var EntityManager
     */
    private $em;
    private $itemsPerPage = 0;
    private $startNumber = 1;
    private $pager = false;
    private $numberOfItems = 0;

    /**
     * construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->queryBuilder = $this->em->createQueryBuilder();
        $this->queryBuilder->select('p')
            ->from('Zikula\PagesModule\Entity\PageEntity', 'p')
            ->leftJoin('p.categoryAssignments', 'c');
    }

    public function setItemsPerPage($amount)
    {
        $this->itemsPerPage = $amount;
    }

    /**
     * set start number
     *
     * @param int $startNumber Start number
     *
     * @return void
     */
    public function setStartNumber($startNumber)
    {
        $this->startNumber = $startNumber - 1;
    }

    /**
     * set order
     *
     * @param string $orderBy E.g. titles
     * @param string $orderDirection ASC/DESC
     *
     * @return void
     */
    public function setOrder($orderBy, $orderDirection = 'ASC')
    {
        $this->queryBuilder->orderBy('p.' . $orderBy, $orderDirection);
    }

    /**
     * set language
     *
     * @param string $language Language code
     *
     * @return void
     */
    public function setLanguage($language)
    {
        $multilingual = System::getVar('multilingual', false);
        if (!empty($language) && $multilingual) {
            $this->queryBuilder->andWhere('p.language = :language')->setParameter('language', $language);
        }
    }

    /**
     * set category
     *
     * @param mixed $category Category id
     *
     * @return void
     */
    public function setCategory($category)
    {
        $subQb = $this->em->createQueryBuilder();
        $categorySubQuery = $subQb->select('pc')
            ->from('ZikulaCategoriesModule:CategoryEntity', 'pc');
        if (is_array($category)) {
            $categorySubQuery->where('pc.id in (:categories)');
        } else {
            if (!empty($category)) {
                $categorySubQuery->where('pc.id = :categories');
            }
        }
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->in('c.category', $categorySubQuery->getDQL()))
            ->setParameter('categories', $category);
    }

    public function setFilterBy(array $filterData)
    {
        if (!empty($filterData['language'])) {
            $this->setLanguage($filterData['language']);
        }
        if (isset($filterData['categoryAssignments']) && ($filterData['categoryAssignments'] instanceof ArrayCollection) && !$filterData['categoryAssignments']->isEmpty()) {
            $categoryIds = array();
            foreach ($filterData['categoryAssignments'] as $pagesCategoryEntity) {
                $categoryIds[] = $pagesCategoryEntity->getCategory()->getId();
            }
            $this->setCategory($categoryIds);
        }
    }

    /**
     * return page as array
     *
     * @return array
     */
    public function get()
    {
        $query = $this->queryBuilder->getQuery();
        if ($this->itemsPerPage > 0) {
            $query->setMaxResults($this->itemsPerPage);
        }
        if ($this->pager) {
            $query->setFirstResult($this->startNumber);
            $paginator = new Paginator($query);
            $this->numberOfItems = count($paginator);

            return $paginator;
        } else {
            return $query->getResult();
        }
    }

    /**
     * enable Pager
     *
     * @return array
     */
    public function enablePager()
    {
        $this->pager = true;
    }

    /**
     * return page as array
     *
     * @return array
     */
    public function getPager()
    {
        return array('itemsperpage' => $this->itemsPerPage, 'numitems' => $this->numberOfItems);
    }
}
