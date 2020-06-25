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

use Zikula\PagesModule\Entity\Base\AbstractPageEntity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entity class that defines the entity structure and behaviours.
 *
 * This is the concrete entity class for page entities.
 * @ORM\Entity(repositoryClass="Zikula\PagesModule\Entity\Repository\PageRepository")
 * @ORM\Table(name="zikula_pages_page",
 *     indexes={
 *         @ORM\Index(name="workflowstateindex", columns={"workflowState"})
 *     }
 * )
 * @UniqueEntity(fields="slug", ignoreNull="false")
 */
class PageEntity extends BaseEntity
{
    // feel free to add your own methods here
}