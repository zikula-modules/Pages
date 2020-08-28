<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 *
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio 1.5.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Helper\Base;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Composite;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Helper\ControllerHelper;
use Zikula\PagesModule\Helper\EntityDisplayHelper;
use Zikula\PagesModule\Helper\PermissionHelper;

/**
 * Search helper base class.
 */
abstract class AbstractSearchHelper implements SearchableInterface
{
    use TranslatorTrait;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var EntityFactory
     */
    protected $entityFactory;
    
    /**
     * @var ControllerHelper
     */
    protected $controllerHelper;
    
    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;
    
    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;
    
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        EntityFactory $entityFactory,
        ControllerHelper $controllerHelper,
        EntityDisplayHelper $entityDisplayHelper,
        PermissionHelper $permissionHelper
    ) {
        $this->setTranslator($translator);
        $this->requestStack = $requestStack;
        $this->entityFactory = $entityFactory;
        $this->controllerHelper = $controllerHelper;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->permissionHelper = $permissionHelper;
    }
    
    public function amendForm(FormBuilderInterface $builder): void
    {
        if (!$this->permissionHelper->hasPermission(ACCESS_READ)) {
            return;
        }
    
        $builder->add('active', HiddenType::class, [
            'data' => true,
        ]);
    
        $searchTypes = $this->getSearchTypes();
    
        foreach ($searchTypes as $searchType => $typeInfo) {
            $builder->add('active_' . $searchType, CheckboxType::class, [
                'data' => true,
                'value' => $typeInfo['value'],
                'label' => $typeInfo['label'],
                'label_attr' => ['class' => 'checkbox-inline'],
                'required' => false,
            ]);
        }
    }
    
    public function getResults(array $words, string $searchType = 'AND', ?array $modVars = null): array
    {
        if (!$this->permissionHelper->hasPermission(ACCESS_READ)) {
            return [];
        }
    
        // initialise array for results
        $results = [];
    
        // retrieve list of activated object types
        $searchTypes = $this->getSearchTypes();
        $entitiesWithDisplayAction = ['page'];
        $request = $this->requestStack->getCurrentRequest();
    
        foreach ($searchTypes as $searchTypeCode => $typeInfo) {
            $isActivated = false;
            $searchSettings = $request->query->get('zikulasearchmodule_search', []);
            $moduleActivationInfo = $searchSettings['modules'];
            if (isset($moduleActivationInfo['ZikulaPagesModule'])) {
                $moduleActivationInfo = $moduleActivationInfo['ZikulaPagesModule'];
                $isActivated = isset($moduleActivationInfo['active_' . $searchTypeCode]);
            }
            if (!$isActivated) {
                continue;
            }
    
            $objectType = $typeInfo['value'];
            $whereArray = [];
            $languageField = null;
            switch ($objectType) {
                case 'page':
                    $whereArray[] = 'tbl.workflowState';
                    $whereArray[] = 'tbl.title';
                    $whereArray[] = 'tbl.metaDescription';
                    $whereArray[] = 'tbl.pageLanguage';
                    $whereArray[] = 'tbl.content';
                    break;
            }
    
            $repository = $this->entityFactory->getRepository($objectType);
    
            // build the search query without any joins
            $qb = $repository->getListQueryBuilder('', '', false);
    
            // build where expression for given search type
            $whereExpr = $this->formatWhere($qb, $words, $whereArray, $searchType);
            $qb->andWhere($whereExpr);
    
            $query = $repository->getQueryFromBuilder($qb);
    
            // set a sensitive limit
            $query->setFirstResult(0)
                  ->setMaxResults(250);
    
            // fetch the results
            $entities = $query->getResult();
    
            if (0 === count($entities)) {
                continue;
            }
    
            $descriptionFieldName = $this->entityDisplayHelper->getDescriptionFieldName($objectType);
            $hasDisplayAction = in_array($objectType, $entitiesWithDisplayAction, true);
    
            $session = $request->hasSession() ? $request->getSession() : null;
            foreach ($entities as $entity) {
                if (!$this->permissionHelper->mayRead($entity)) {
                    continue;
                }
    
                $description = !empty($descriptionFieldName) ? strip_tags($entity[$descriptionFieldName]) : '';
                $created = $entity['createdDate'] ?? null;
    
                $formattedTitle = $this->entityDisplayHelper->getFormattedTitle($entity);
                $displayUrl = null;
                if ($hasDisplayAction) {
                    $urlArgs = $entity->createUrlArgs();
                    $urlArgs['_locale'] = null !== $languageField && !empty($entity[$languageField])
                        ? $entity[$languageField]
                        : $request->getLocale()
                    ;
                    $displayUrl = new RouteUrl('zikulapagesmodule_' . mb_strtolower($objectType) . '_display', $urlArgs);
                }
    
                $result = new SearchResultEntity();
                $result->setTitle($formattedTitle)
                    ->setText($description)
                    ->setModule($this->getBundleName())
                    ->setCreated($created)
                    ->setSesid(null !== $session ? $session->getId() : null)
                ;
                if (null !== $displayUrl) {
                    $result->setUrl($displayUrl);
                }
                $results[] = $result;
            }
        }
    
        return $results;
    }
    
    /**
     * Returns list of supported search types.
     */
    protected function getSearchTypes(): array
    {
        $searchTypes = [
            'zikulaPagesModulePages' => [
                'value' => 'page',
                'label' => $this->trans('Pages', [], 'page'),
            ],
        ];
    
        $allowedTypes = $this->controllerHelper->getObjectTypes(
            'helper',
            ['helper' => 'search', 'action' => 'getSearchTypes']
        );
        $allowedSearchTypes = [];
        foreach ($searchTypes as $searchType => $typeInfo) {
            if (!in_array($typeInfo['value'], $allowedTypes, true)) {
                continue;
            }
            if (!$this->permissionHelper->hasComponentPermission($typeInfo['value'], ACCESS_READ)) {
                continue;
            }
            $allowedSearchTypes[$searchType] = $typeInfo;
        }
    
        return $allowedSearchTypes;
    }
    
    public function getErrors(): array
    {
        return [];
    }
    
    /**
     * Construct a QueryBuilder Where orX|andX Expr instance.
     */
    protected function formatWhere(
        QueryBuilder $qb,
        array $words = [],
        array $fields = [],
        string $searchtype = 'AND'
    ): ?Composite {
        if (empty($words) || empty($fields)) {
            return null;
        }
    
        $method = 'OR' === $searchtype ? 'orX' : 'andX';
        /** @var $where Composite */
        $where = $qb->expr()->$method();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            foreach ($fields as $field) {
                $expr = $qb->expr()->like($field, "?$i");
                $subWhere->add($expr);
                $qb->setParameter($i, '%' . $word . '%');
                ++$i;
            }
            $where->add($subWhere);
        }
    
        return $where;
    }
    
    public function getBundleName(): string
    {
        return 'ZikulaPagesModule';
    }
}
