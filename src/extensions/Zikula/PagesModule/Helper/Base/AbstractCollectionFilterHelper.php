<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Helper\Base;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\PagesModule\Helper\CategoryHelper;
use Zikula\PagesModule\Helper\PermissionHelper;

/**
 * Entity collection filter helper base class.
 */
abstract class AbstractCollectionFilterHelper
{
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;
    
    /**
     * @var CurrentUserApiInterface
     */
    protected $currentUserApi;
    
    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var bool Fallback value to determine whether only own entries should be selected or not
     */
    protected $showOnlyOwnEntries = false;
    
    /**
     * @var bool Whether to apply a locale-based filter or not
     */
    protected $filterDataByLocale = false;
    
    public function __construct(
        RequestStack $requestStack,
        PermissionHelper $permissionHelper,
        CurrentUserApiInterface $currentUserApi,
        CategoryHelper $categoryHelper,
        VariableApiInterface $variableApi
    ) {
        $this->requestStack = $requestStack;
        $this->permissionHelper = $permissionHelper;
        $this->currentUserApi = $currentUserApi;
        $this->categoryHelper = $categoryHelper;
        $this->variableApi = $variableApi;
        $this->showOnlyOwnEntries = (bool)$variableApi->get('ZikulaPagesModule', 'showOnlyOwnEntries');
        $this->filterDataByLocale = (bool)$variableApi->get('ZikulaPagesModule', 'filterDataByLocale');
    }
    
    /**
     * Returns an array of additional template variables for view quick navigation forms.
     */
    public function getViewQuickNavParameters(string $objectType = '', string $context = '', array $args = []): array
    {
        if (!in_array($context, ['controllerAction', 'api', 'actionHandler', 'block', 'contentType'], true)) {
            $context = 'controllerAction';
        }
    
        if ('page' === $objectType) {
            return $this->getViewQuickNavParametersForPage($context, $args);
        }
    
        return [];
    }
    
    /**
     * Adds quick navigation related filter options as where clauses.
     */
    public function addCommonViewFilters(string $objectType, QueryBuilder $qb): QueryBuilder
    {
        if ('page' === $objectType) {
            return $this->addCommonViewFiltersForPage($qb);
        }
    
        return $qb;
    }
    
    /**
     * Adds default filters as where clauses.
     */
    public function applyDefaultFilters(string $objectType, QueryBuilder $qb, array $parameters = []): QueryBuilder
    {
        if ('page' === $objectType) {
            return $this->applyDefaultFiltersForPage($qb, $parameters);
        }
    
        return $qb;
    }
    
    /**
     * Returns an array of additional template variables for view quick navigation forms.
     */
    protected function getViewQuickNavParametersForPage(string $context = '', array $args = []): array
    {
        $parameters = [];
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $parameters;
        }
    
        $parameters['catId'] = $request->query->get('catId', '');
        $parameters['catIdList'] = $this->categoryHelper->retrieveCategoriesFromRequest('page', 'GET');
        $parameters['workflowState'] = $request->query->get('workflowState', '');
        $parameters['pageLanguage'] = $request->query->get('pageLanguage', '');
        $parameters['q'] = $request->query->get('q', '');
        $parameters['active'] = $request->query->get('active', '');
        $parameters['displayWrapper'] = $request->query->get('displayWrapper', '');
        $parameters['displayTitle'] = $request->query->get('displayTitle', '');
        $parameters['displayCreated'] = $request->query->get('displayCreated', '');
        $parameters['displayUpdated'] = $request->query->get('displayUpdated', '');
        $parameters['displayTextInfo'] = $request->query->get('displayTextInfo', '');
        $parameters['displayPrint'] = $request->query->get('displayPrint', '');
    
        return $parameters;
    }
    
    /**
     * Adds quick navigation related filter options as where clauses.
     */
    protected function addCommonViewFiltersForPage(QueryBuilder $qb): QueryBuilder
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $qb;
        }
        $routeName = $request->get('_route', '');
        if (false !== strpos($routeName, 'edit')) {
            return $qb;
        }
    
        $parameters = $this->getViewQuickNavParametersForPage();
        foreach ($parameters as $k => $v) {
            if (null === $v) {
                continue;
            }
            if ('catId' === $k) {
                if (0 < (int)$v) {
                    // single category filter
                    $qb->andWhere('tblCategories.category = :category')
                       ->setParameter('category', $v);
                }
                continue;
            }
            if ('catIdList' === $k) {
                // multi category filter
                $qb = $this->categoryHelper->buildFilterClauses($qb, 'page', $v);
                continue;
            }
            if (in_array($k, ['q', 'searchterm'], true)) {
                // quick search
                if (!empty($v)) {
                    $qb = $this->addSearchFilter('page', $qb, $v);
                }
                continue;
            }
            if (in_array($k, ['active', 'displayWrapper', 'displayTitle', 'displayCreated', 'displayUpdated', 'displayTextInfo', 'displayPrint'], true)) {
                // boolean filter
                if ('no' === $v) {
                    $qb->andWhere('tbl.' . $k . ' = 0');
                } elseif ('yes' === $v || '1' === $v) {
                    $qb->andWhere('tbl.' . $k . ' = 1');
                }
                continue;
            }
    
            if (is_array($v)) {
                continue;
            }
    
            // field filter
            if ((!is_numeric($v) && '' !== $v) || (is_numeric($v) && 0 < $v)) {
                $v = (string)$v;
                if ('workflowState' === $k && 0 === strpos($v, '!')) {
                    $qb->andWhere('tbl.' . $k . ' != :' . $k)
                       ->setParameter($k, substr($v, 1));
                } elseif (0 === strpos($v, '%')) {
                    $qb->andWhere('tbl.' . $k . ' LIKE :' . $k)
                       ->setParameter($k, '%' . substr($v, 1) . '%');
                } else {
                    $qb->andWhere('tbl.' . $k . ' = :' . $k)
                       ->setParameter($k, $v);
                }
            }
        }
    
        return $this->applyDefaultFiltersForPage($qb, $parameters);
    }
    
    /**
     * Adds default filters as where clauses.
     */
    protected function applyDefaultFiltersForPage(QueryBuilder $qb, array $parameters = []): QueryBuilder
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $qb;
        }
    
        $showOnlyOwnEntries = (bool)$request->query->getInt('own', (int) $this->showOnlyOwnEntries);
        $privateMode = (bool)$this->variableApi->get('ZikulaPagesModule', 'pagePrivateMode', false);
        if ($privateMode) {
            $showOnlyOwnEntries = true;
        }
        if ($showOnlyOwnEntries) {
            $qb = $this->addCreatorFilter($qb);
        }
    
        $routeName = $request->get('_route', '');
        $isAdminArea = false !== strpos($routeName, 'zikulapagesmodule_page_admin');
        if ($isAdminArea) {
            return $qb;
        }
    
        if (!array_key_exists('workflowState', $parameters) || empty($parameters['workflowState'])) {
            // per default we show approved pages only
            $onlineStates = ['approved'];
            if ($showOnlyOwnEntries) {
                // allow the owner to see his pages
                $onlineStates[] = 'deferred';
                $onlineStates[] = 'trashed';
            }
            $qb->andWhere('tbl.workflowState IN (:onlineStates)')
               ->setParameter('onlineStates', $onlineStates);
        }
    
        if (true === (bool)$this->filterDataByLocale) {
            $allowedLocales = ['', $request->getLocale()];
            if (!array_key_exists('pageLanguage', $parameters) || empty($parameters['pageLanguage'])) {
                $qb->andWhere('tbl.pageLanguage IN (:currentPageLanguage)')
                   ->setParameter('currentPageLanguage', $allowedLocales);
            }
        }
    
        return $qb;
    }
    
    /**
     * Adds a where clause for search query.
     */
    public function addSearchFilter(string $objectType, QueryBuilder $qb, string $fragment = ''): QueryBuilder
    {
        if ('' === $fragment) {
            return $qb;
        }
    
        $filters = [];
        $parameters = [];
    
        if ('page' === $objectType) {
            $filters[] = 'tbl.workflowState = :searchWorkflowState';
            $parameters['searchWorkflowState'] = $fragment;
            $filters[] = 'tbl.title LIKE :searchTitle';
            $parameters['searchTitle'] = '%' . $fragment . '%';
            $filters[] = 'tbl.metaDescription LIKE :searchMetaDescription';
            $parameters['searchMetaDescription'] = '%' . $fragment . '%';
            $filters[] = 'tbl.pageLanguage LIKE :searchPageLanguage';
            $parameters['searchPageLanguage'] = '%' . $fragment . '%';
            $filters[] = 'tbl.content LIKE :searchContent';
            $parameters['searchContent'] = '%' . $fragment . '%';
            if (is_numeric($fragment)) {
                $filters[] = 'tbl.counter = :searchCounter';
                $parameters['searchCounter'] = $fragment;
            }
        }
    
        $qb->andWhere('(' . implode(' OR ', $filters) . ')');
    
        foreach ($parameters as $parameterName => $parameterValue) {
            $qb->setParameter($parameterName, $parameterValue);
        }
    
        return $qb;
    }
    
    /**
     * Adds a filter for the createdBy field.
     */
    public function addCreatorFilter(QueryBuilder $qb, int $userId = null): QueryBuilder
    {
        if (null === $userId) {
            $userId = $this->currentUserApi->isLoggedIn()
                ? (int)$this->currentUserApi->get('uid')
                : UsersConstant::USER_ID_ANONYMOUS
            ;
        }
    
        $qb->andWhere('tbl.createdBy = :userId')
           ->setParameter('userId', $userId);
    
        return $qb;
    }
}
