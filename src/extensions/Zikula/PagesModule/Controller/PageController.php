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

namespace Zikula\PagesModule\Controller;

use Zikula\PagesModule\Controller\Base\AbstractPageController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Form\Handler\Page\EditHandler;
use Zikula\PagesModule\Helper\ControllerHelper;
use Zikula\PagesModule\Helper\HookHelper;
use Zikula\PagesModule\Helper\PermissionHelper;
use Zikula\PagesModule\Helper\ViewHelper;
use Zikula\PagesModule\Helper\WorkflowHelper;

/**
 * Page controller class providing navigation and interaction functionality.
 */
class PageController extends AbstractPageController
{
    /**
     *
     * @Route("/admin/pages",
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     */
    public function adminIndexAction(
        Request $request,
        PermissionHelper $permissionHelper
    ): Response {
        return $this->indexInternal(
            $request,
            $permissionHelper,
            true
        );
    }
    
    /**
     *
     * @Route("/pages",
     *        methods = {"GET"}
     * )
     */
    public function indexAction(
        Request $request,
        PermissionHelper $permissionHelper
    ): Response {
        return $this->indexInternal(
            $request,
            $permissionHelper,
            false
        );
    }
    
    /**
     *
     * @Route("/admin/pages/view/{sort}/{sortdir}/{page}/{num}.{_format}",
     *        requirements = {"sortdir" = "asc|desc|ASC|DESC", "page" = "\d+", "num" = "\d+", "_format" = "html|rss|atom"},
     *        defaults = {"sort" = "", "sortdir" = "asc", "page" = 1, "num" = 10, "_format" = "html"},
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     */
    public function adminViewAction(
        Request $request,
        RouterInterface $router,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        string $sort,
        string $sortdir,
        int $page,
        int $num
    ): Response {
        return $this->viewInternal(
            $request,
            $router,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $sort,
            $sortdir,
            $page,
            $num,
            true
        );
    }
    
    /**
     *
     * @Route("/pages/view/{sort}/{sortdir}/{page}/{num}.{_format}",
     *        requirements = {"sortdir" = "asc|desc|ASC|DESC", "page" = "\d+", "num" = "\d+", "_format" = "html|rss|atom"},
     *        defaults = {"sort" = "", "sortdir" = "asc", "page" = 1, "num" = 10, "_format" = "html"},
     *        methods = {"GET"}
     * )
     */
    public function viewAction(
        Request $request,
        RouterInterface $router,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        string $sort,
        string $sortdir,
        int $page,
        int $num
    ): Response {
        return $this->viewInternal(
            $request,
            $router,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $sort,
            $sortdir,
            $page,
            $num,
            false
        );
    }
    
    /**
     *
     * @Route("/admin/page/edit/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"id" = "0", "_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     */
    public function adminEditAction(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EditHandler $formHandler
    ): Response {
        return $this->editInternal(
            $request,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $formHandler,
            true
        );
    }
    
    /**
     *
     * @Route("/page/edit/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"id" = "0", "_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     */
    public function editAction(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EditHandler $formHandler
    ): Response {
        return $this->editInternal(
            $request,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $formHandler,
            false
        );
    }
    
    /**
     * @Route("/print/{slug}")
     * @Theme("print")
     */
    public function displayPrintableAction(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EntityFactory $entityFactory,
        PageEntity $page = null,
        string $slug = ''
    ): Response {
        return $this->displayInternal(
            $request,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $entityFactory,
            $page,
            $slug,
            false
        );
    }
    
    /**
     *
     * @Route("/admin/page/{slug}.{_format}",
     *        requirements = {"slug" = "[^/.]+", "_format" = "html"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     */
    public function adminDisplayAction(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EntityFactory $entityFactory,
        PageEntity $page = null,
        string $slug = ''
    ): Response {
        return $this->displayInternal(
            $request,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $entityFactory,
            $page,
            $slug,
            true
        );
    }
    
    /**
     *
     * @Route("/page/{slug}.{_format}",
     *        requirements = {"slug" = "[^/.]+", "_format" = "html"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET"}
     * )
     */
    public function displayAction(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EntityFactory $entityFactory,
        PageEntity $page = null,
        string $slug = ''
    ): Response {
        return $this->displayInternal(
            $request,
            $permissionHelper,
            $controllerHelper,
            $viewHelper,
            $entityFactory,
            $page,
            $slug,
            false
        );
    }
    
    /**
     * Process status changes for multiple items.
     *
     * @Route("/admin/pages/handleSelectedEntries",
     *        methods = {"POST"}
     * )
     * @Theme("admin")
     */
    public function adminHandleSelectedEntriesAction(
        Request $request,
        LoggerInterface $logger,
        EntityFactory $entityFactory,
        WorkflowHelper $workflowHelper,
        HookHelper $hookHelper,
        CurrentUserApiInterface $currentUserApi
    ): RedirectResponse {
        return $this->handleSelectedEntriesActionInternal(
            $request,
            $logger,
            $entityFactory,
            $workflowHelper,
            $hookHelper,
            $currentUserApi,
            true
        );
    }
    
    /**
     * Process status changes for multiple items.
     *
     * @Route("/pages/handleSelectedEntries",
     *        methods = {"POST"}
     * )
     */
    public function handleSelectedEntriesAction(
        Request $request,
        LoggerInterface $logger,
        EntityFactory $entityFactory,
        WorkflowHelper $workflowHelper,
        HookHelper $hookHelper,
        CurrentUserApiInterface $currentUserApi
    ): RedirectResponse {
        return $this->handleSelectedEntriesActionInternal(
            $request,
            $logger,
            $entityFactory,
            $workflowHelper,
            $hookHelper,
            $currentUserApi,
            false
        );
    }
    
    // feel free to add your own controller methods here
}