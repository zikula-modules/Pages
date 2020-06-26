<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.5.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\PagesModule\Controller\Base\AbstractExternalController;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Helper\CollectionFilterHelper;
use Zikula\PagesModule\Helper\ControllerHelper;
use Zikula\PagesModule\Helper\ListEntriesHelper;
use Zikula\PagesModule\Helper\PermissionHelper;
use Zikula\PagesModule\Helper\ViewHelper;

/**
 * Controller for external calls implementation class.
 *
 * @Route("/external")
 */
class ExternalController extends AbstractExternalController
{
    /**
     * @Route("/display/{objectType}/{id}/{source}/{displayMode}",
     *        requirements = {"id" = "\d+", "source" = "block|contentType|scribite", "displayMode" = "link|embed"},
     *        defaults = {"source" = "contentType", "displayMode" = "embed"},
     *        methods = {"GET"}
     * )
     */
    public function displayAction(
        Request $request,
        ControllerHelper $controllerHelper,
        PermissionHelper $permissionHelper,
        EntityFactory $entityFactory,
        ViewHelper $viewHelper,
        string $objectType,
        int $id,
        string $source,
        string $displayMode
    ): Response {
        return parent::displayAction(
            $request,
            $controllerHelper,
            $permissionHelper,
            $entityFactory,
            $viewHelper,
            $objectType,
            $id,
            $source,
            $displayMode
        );
    }

    /**
     * @Route("/finder/{objectType}/{editor}/{sort}/{sortdir}/{page}/{num}",
     *        requirements = {"editor" = "ckeditor|quill|summernote|tinymce", "sortdir" = "asc|desc", "page" = "\d+", "num" = "\d+"},
     *        defaults = {"sort" = "dummy", "sortdir" = "asc", "page" = 1, "num" = 0},
     *        methods = {"GET"},
     *        options={"expose"=true}
     * )
     */
    public function finderAction(
        Request $request,
        RouterInterface $router,
        ControllerHelper $controllerHelper,
        PermissionHelper $permissionHelper,
        EntityFactory $entityFactory,
        CollectionFilterHelper $collectionFilterHelper,
        ListEntriesHelper $listEntriesHelper,
        ViewHelper $viewHelper,
        AssetBag $cssAssetBag,
        Asset $assetHelper,
        string $objectType,
        string $editor,
        string $sort,
        string $sortdir,
        int $page = 1,
        int $num = 0
    ): Response {
        return parent::finderAction(
            $request,
            $router,
            $controllerHelper,
            $permissionHelper,
            $entityFactory,
            $collectionFilterHelper,
            $listEntriesHelper,
            $viewHelper,
            $cssAssetBag,
            $assetHelper,
            $objectType,
            $editor,
            $sort,
            $sortdir,
            $page,
            $num
        );
    }

    // feel free to extend the external controller here
}
