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

namespace Zikula\PagesModule\Entity;

use Zikula\PagesModule\Entity\Base\AbstractPageCategoryEntity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity extension domain class storing page categories.
 *
 * This is the concrete category class for page entities.
 * @ORM\Entity(repositoryClass="\Zikula\PagesModule\Entity\Repository\PageCategoryRepository")
 * @ORM\Table(name="zikula_pages_page_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cat_unq", columns={"registryId", "categoryId", "entityId"})
 *     }
 * )
 */
class PageCategoryEntity extends BaseEntity
{
    // feel free to add your own methods here
}
