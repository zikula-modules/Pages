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

namespace Zikula\PagesModule\Controller\Base;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\PagesModule\Entity\Factory\EntityFactory;
use Zikula\PagesModule\Helper\CollectionFilterHelper;
use Zikula\PagesModule\Helper\ControllerHelper;
use Zikula\PagesModule\Helper\ListEntriesHelper;
use Zikula\PagesModule\Helper\PermissionHelper;
use Zikula\PagesModule\Helper\ViewHelper;

/**
 * Controller for external calls base class.
 */
abstract class AbstractExternalController extends AbstractController
{
    /**
     * Displays one item of a certain object type using a separate template for external usages.
     *
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
        $contextArgs = ['controller' => 'external', 'action' => 'display'];
        if (!in_array($objectType, $controllerHelper->getObjectTypes('controllerAction', $contextArgs), true)) {
            $objectType = $controllerHelper->getDefaultObjectType('controllerAction', $contextArgs);
        }
        
        $repository = $entityFactory->getRepository($objectType);
        
        // assign object data fetched from the database
        $entity = $repository->selectById($id);
        if (null === $entity) {
            return new Response($this->trans('No such item.'));
        }
        
        if (!$permissionHelper->mayRead($entity)) {
            return new Response('');
        }
        
        $template = $request->query->get('template');
        if (null === $template || '' === $template) {
            $template = 'display.html.twig';
        }
        
        $templateParameters = [
            'objectType' => $objectType,
            'source' => $source,
            $objectType => $entity,
            'displayMode' => $displayMode,
        ];
        
        $contextArgs = ['controller' => 'external', 'action' => 'display'];
        $templateParameters = $controllerHelper->addTemplateParameters(
            $objectType,
            $templateParameters,
            'controllerAction',
            $contextArgs
        );
        
        $request->query->set('raw', true);
        
        return $viewHelper->processTemplate(
            'external',
            ucfirst($objectType) . '/' . str_replace('.html.twig', '', $template),
            $templateParameters
        );
    }
    
    /**
     * Popup selector for Scribite plugins.
     * Finds items of a certain object type.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
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
        $activatedObjectTypes = $listEntriesHelper->extractMultiList($this->getVar('enabledFinderTypes', ''));
        if (!in_array($objectType, $activatedObjectTypes, true)) {
            if (!count($activatedObjectTypes)) {
                throw new AccessDeniedException();
            }
        
            // redirect to first valid object type
            $redirectUrl = $router->generate(
                'zikulapagesmodule_external_finder',
                ['objectType' => array_shift($activatedObjectTypes), 'editor' => $editor]
            );
        
            return new RedirectResponse($redirectUrl);
        }
        
        $formData = $request->query->get('zikulapagesmodule_' . mb_strtolower($objectType) . 'finder', []);
        
        if (!$permissionHelper->hasComponentPermission($objectType, ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }
        
        if (empty($editor) || !in_array($editor, ['ckeditor', 'quill', 'summernote', 'tinymce'], true)) {
            return new Response($this->trans('Error: Invalid editor context given for external controller action.'));
        }
        
        $cssAssetBag->add($assetHelper->resolve('@ZikulaPagesModule:css/style.css'));
        $cssAssetBag->add([$assetHelper->resolve('@ZikulaPagesModule:css/custom.css') => 120]);
        
        $repository = $entityFactory->getRepository($objectType);
        if (empty($sort) || !in_array($sort, $repository->getAllowedSortingFields(), true)) {
            $sort = $repository->getDefaultSortingField();
        }
        
        $sdir = mb_strtolower($sortdir);
        if ('asc' !== $sdir && 'desc' !== $sdir) {
            $sdir = 'asc';
        }
        
        // the number of items displayed on a page for pagination
        $resultsPerPage = $num;
        if (0 === $resultsPerPage) {
            $resultsPerPage = $this->getVar($objectType . 'EntriesPerPage', 20);
        }
        
        $templateParameters = [
            'editorName' => $editor,
            'objectType' => $objectType,
            'sort' => $sort,
            'sortdir' => $sdir,
            'currentPage' => $page,
            'language' => isset($formData['language']) ? $formData['language'] : $request->getLocale(),
        ];
        $searchTerm = '';
        
        $formOptions = [
            'object_type' => $objectType,
            'editor_name' => $editor,
        ];
        $form = $this->createForm(
            'Zikula\PagesModule\Form\Type\Finder\\' . ucfirst($objectType) . 'FinderType',
            $templateParameters,
            $formOptions
        );
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $templateParameters = array_merge($templateParameters, $formData);
            $page = $formData['currentPage'];
            $resultsPerPage = $formData['num'];
            $sort = $formData['sort'];
            $sdir = $formData['sortdir'];
            $searchTerm = $formData['q'];
        }
        
        $where = '';
        $orderBy = $sort . ' ' . $sdir;
        
        $qb = $repository->getListQueryBuilder($where, $orderBy);
        
        if ('' !== $searchTerm) {
            $qb = $this->$collectionFilterHelper->addSearchFilter($objectType, $qb, $searchTerm);
        }
        
        $paginator = $repository->retrieveCollectionResult($qb, true, $page, $resultsPerPage);
        $paginator->setRoute('zikulapagesmodule_external_finder');
        $paginator->setRouteParameters($formData);
        
        $templateParameters['paginator'] = $paginator;
        $entities = $paginator->getResults();
        
        // filter by permissions
        $entities = $permissionHelper->filterCollection($objectType, $entities, ACCESS_READ);
        
        $templateParameters['items'] = $entities;
        $templateParameters['finderForm'] = $form->createView();
        
        $contextArgs = ['controller' => 'external', 'action' => 'display'];
        $templateParameters = $controllerHelper->addTemplateParameters(
            $objectType,
            $templateParameters,
            'controllerAction',
            $contextArgs
        );
        
        $templateParameters['activatedObjectTypes'] = $activatedObjectTypes;
        $request->query->set('raw', true);
        
        return $viewHelper->processTemplate('external', ucfirst($objectType) . '/find', $templateParameters);
    }
}
