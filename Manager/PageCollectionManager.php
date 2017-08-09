<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManager;
use Zikula\PagesModule\Entity\PageEntity;

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
     */
    public function setOrder($orderBy, $orderDirection = 'ASC')
    {
        $this->queryBuilder->orderBy('p.' . $orderBy, $orderDirection);
    }

    /**
     * set language
     *
     * @param string $language Language code
     */
    public function setLanguage($language)
    {
        if (!empty($language)) {
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
            $categoryIds = [];
            foreach ($filterData['categoryAssignments'] as $pagesCategoryEntity) {
                $categoryIds[] = $pagesCategoryEntity->getCategory()->getId();
            }
            $this->setCategory($categoryIds);
        }
    }

    /**
     * return array of pages
     *
     * @return PageEntity[]
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
        return ['itemsperpage' => $this->itemsPerPage, 'numitems' => $this->numberOfItems];
    }
}
