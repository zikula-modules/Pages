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

namespace Zikula\PagesModule\Helper;

use Doctrine\ORM\QueryBuilder;
use Zikula\PagesModule\Helper\Base\AbstractCollectionFilterHelper;

/**
 * Entity collection filter helper implementation class.
 */
class CollectionFilterHelper extends AbstractCollectionFilterHelper
{
    protected function applyDefaultFiltersForPage(QueryBuilder $qb, array $parameters = []): QueryBuilder
    {
        $qb = parent::applyDefaultFiltersForPage($qb, $parameters);

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $routeName = $request->get('_route', '');
            $isAdminArea = false !== mb_strpos($routeName, 'zikulapagesmodule_page_admin');
            if ($isAdminArea) {
                return $qb;
            }
        }

        $qb->andWhere('tbl.active = 1');
        
        return $qb;
    }
}
