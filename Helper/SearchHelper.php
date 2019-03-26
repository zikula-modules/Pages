<?php

declare(strict_types=1);
/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\CategoriesModule\Api\ApiInterface\CategoryPermissionApiInterface;
use Zikula\Core\RouteUrl;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var bool
     */
    private $enableCategorization;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CategoryPermissionApiInterface
     */
    private $categoryPermissionApi;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * SearchHelper constructor.
     * @param PermissionApiInterface $permissionApi
     * @param EntityManagerInterface $entityManager
     * @param CategoryPermissionApiInterface $categoryPermissionApi
     * @param SessionInterface $session
     * @param bool $enableCategorization
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        EntityManagerInterface $entityManager,
        CategoryPermissionApiInterface $categoryPermissionApi,
        SessionInterface $session,
        $enableCategorization
    ) {
        $this->permissionApi = $permissionApi;
        $this->entityManager = $entityManager;
        $this->categoryPermissionApi = $categoryPermissionApi;
        $this->session = $session;
        $this->enableCategorization = $enableCategorization;
    }

    /**
     * {@inheritdoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        // not needed because `active` child object is already added and that is all that is needed.
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        if (!$this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_READ)) {
            return [];
        }
        $method = ('OR' === $searchType) ? 'orX' : 'andX';
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from('Zikula\PagesModule\Entity\PageEntity', 'p');
        /** @var $where \Doctrine\ORM\Query\Expr\Composite */
        $where = $qb->expr()->{$method}();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            foreach (['p.title', 'p.content'] as $field) {
                $expr = $qb->expr()->like($field, "?${i}");
                $subWhere->add($expr);
                $qb->setParameter($i, '%' . $word . '%');
                $i++;
            }
            $where->add($subWhere);
        }
        $qb->andWhere($where);
        $pages = $qb->getQuery()->getResult();

        $results = [];
        /** @var $pages \Zikula\PagesModule\Entity\PageEntity[] */
        foreach ($pages as $page) {
            $pagePermissionCheck = $this->permissionApi->hasPermission('ZikulaPagesModule::', $page->getTitle() . '::' . $page->getPageid(), ACCESS_OVERVIEW);
            if ($this->enableCategorization) {
                $pagePermissionCheck = $pagePermissionCheck && $this->categoryPermissionApi->hasCategoryAccess($page->getCategoryAssignments()->toArray());
            }
            if (!$pagePermissionCheck) {
                continue;
            }
            $result = new SearchResultEntity();
            $result->setTitle($page->getTitle())
                ->setText($page->getContent())
                ->setModule('ZikulaPagesModule')
                ->setCreated($page->getCr_date())
                ->setUrl(RouteUrl::createFromRoute('zikulapagesmodule_user_display', ['urltitle' => $page->getUrltitle()]))
                ->setSesid($this->session->getId());
            $results[] = $result;
        }

        return $results;
    }

    public function getErrors()
    {
        return [];
    }
}
