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

namespace Zikula\PagesModule\Twig;

use Twig\TwigFunction;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Helper\CategoryHelper;
use Zikula\PagesModule\Helper\CollectionFilterHelper;
use Zikula\PagesModule\Twig\Base\AbstractTwigExtension;

/**
 * Twig extension implementation class.
 */
class TwigExtension extends AbstractTwigExtension
{
    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var CollectionFilterHelper
     */
    private $collectionFilterHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function getFunctions()
    {
        $functions = parent::getFunctions();

        $functions[] = new TwigFunction('zikulapagesmodule_categoryInfo', [$this, 'getCategoryInfo']);

        return $functions;
    }

    /**
     * The zikulapagesmodule_categoryInfo function returns all main categories
     * together with the amount of included pages.
     * Examples:
     *    {% set categoryInfoPerRegistry = zikulapagesmodule_categoryInfo() %}.
     */
    public function getCategoryInfo(): array
    {
        $properties = $this->categoryHelper->getAllPropertiesWithMainCat('page');
        if (!count($properties)) {
            return [];
        }

        $categoryInfoPerRegistry = [];
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $pageRepository = $this->entityFactory->getRepository('page');

        foreach ($properties as $categoryId) {
            $baseCategory = $this->categoryRepository->find($categoryId);
            if (null === $baseCategory) {
                continue;
            }
            $displayName = $baseCategory->getDisplayName();
            $registryLabel = $displayName[$locale] ?? $displayName['en'];

            $categories = $baseCategory->getChildren();
            $pageCounts = [];
            /** @var CategoryEntity $category */
            foreach ($categories as $category) {
                $qb = $pageRepository->getCountQuery('', false);
                $qb = $this->collectionFilterHelper->applyDefaultFilters('page', $qb);
                $qb->leftJoin('tbl.categories', 'tblCategories')
                    ->andWhere('tblCategories.category = :category')
                    ->setParameter('category', $category->getId());
                $pageCount = $qb->getQuery()->getSingleScalarResult();
                $pageCounts[$category->getId()] = $pageCount;
            }

            $categoryInfoPerRegistry[$registryLabel] = [
                'categories' => $categories,
                'pageCounts' => $pageCounts,
            ];
        }

        return $categoryInfoPerRegistry;
    }

    /**
     * @required
     */
    public function setAdditionalDependencies(
        CategoryHelper $categoryHelper,
        EntityFactory $entityFactory,
        CollectionFilterHelper $collectionFilterHelper,
        CategoryRepositoryInterface $categoryRepository
    ): void {
        $this->categoryHelper = $categoryHelper;
        $this->entityFactory = $entityFactory;
        $this->collectionFilterHelper = $collectionFilterHelper;
        $this->categoryRepository = $categoryRepository;
    }
}
