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

namespace Zikula\PagesModule\Event\Base;

use Zikula\PagesModule\Entity\PageEntity;

/**
 * Event base class for filtering page processing.
 */
class AbstractPagePreRemoveEvent
{
    /**
     * @var PageEntity Reference to treated entity instance.
     */
    protected $page;

    public function __construct(PageEntity $page)
    {
        $this->page = $page;
    }

    /**
     * @return PageEntity
     */
    public function getPage(): PageEntity
    {
        return $this->page;
    }
}
