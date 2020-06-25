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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PagesModule\Helper\ControllerHelper;
use Zikula\PagesModule\Helper\PermissionHelper;

/**
 * Helper base class for view layer methods.
 */
abstract class AbstractViewHelper
{
    /**
     * @var Environment
     */
    protected $twig;
    
    /**
     * @var LoaderInterface
     */
    protected $twigLoader;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var ControllerHelper
     */
    protected $controllerHelper;
    
    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;
    
    public function __construct(
        Environment $twig,
        LoaderInterface $twigLoader,
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        ControllerHelper $controllerHelper,
        PermissionHelper $permissionHelper
    ) {
        $this->twig = $twig;
        $this->twigLoader = $twigLoader;
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->controllerHelper = $controllerHelper;
        $this->permissionHelper = $permissionHelper;
    }
    
    /**
     * Determines the view template for a certain method with given parameters.
     */
    public function getViewTemplate(string $type, string $func): string
    {
        // create the base template name
        $template = '@ZikulaPagesModule/' . ucfirst($type) . '/' . $func;
    
        // check for template extension
        $templateExtension = '.' . $this->determineExtension($type, $func);
    
        // check whether a special template is used
        $request = $this->requestStack->getCurrentRequest();
        $tpl = null !== $request ? $request->query->getAlnum('tpl') : '';
        if (!empty($tpl)) {
            // check if custom template exists
            $customTemplate = $template . ucfirst($tpl);
            if ($this->twigLoader->exists($customTemplate . $templateExtension)) {
                $template = $customTemplate;
            }
        }
    
        $template .= $templateExtension;
    
        return $template;
    }
    
    /**
     * Helper method for managing view templates.
     */
    public function processTemplate(
        string $type,
        string $func,
        array $templateParameters = [],
        string $template = ''
    ): Response {
        $templateExtension = $this->determineExtension($type, $func);
        if (empty($template)) {
            $template = $this->getViewTemplate($type, $func);
        }
    
        // look whether we need output with or without the theme
        $request = $this->requestStack->getCurrentRequest();
        $raw = null !== $request ? $request->query->getBoolean('raw') : false;
        if (!$raw && 'html.twig' !== $templateExtension) {
            $raw = true;
        }
    
        $output = $this->twig->render($template, $templateParameters);
        $response = null;
        if (true === $raw) {
            // standalone output
    
            $response = new PlainResponse($output);
        } else {
            // normal output
            $response = new Response($output);
        }
    
        // check if we need to set any custom headers
        switch ($templateExtension) {
            case 'atom.twig':
                $response->headers->set('Content-Type', 'application/atom+xml');
                break;
            case 'rss.twig':
                $response->headers->set('Content-Type', 'application/rss+xml');
                break;
        }
    
        return $response;
    }
    
    /**
     * Get extension of the currently treated template.
     */
    protected function determineExtension(string $type, string $func): string
    {
        $templateExtension = 'html.twig';
        if (!in_array($func, ['view', 'display'])) {
            return $templateExtension;
        }
    
        $extensions = $this->availableExtensions($type, $func);
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $templateExtension;
        }
    
        $format = $request->getRequestFormat();
        if ('html' !== $format && in_array($format, $extensions, true)) {
            $templateExtension = $format . '.twig';
        }
    
        return $templateExtension;
    }
    
    /**
     * Get list of available template extensions.
     *
     * @return string[] List of allowed template extensions
     */
    protected function availableExtensions(string $type, string $func): array
    {
        $extensions = [];
        $hasAdminAccess = $this->permissionHelper->hasComponentPermission($type, ACCESS_ADMIN);
        if ('view' === $func) {
            if ($hasAdminAccess) {
                $extensions = ['rss', 'atom'];
            } else {
                $extensions = ['rss', 'atom'];
            }
        } elseif ('display' === $func) {
            if ($hasAdminAccess) {
                $extensions = [];
            } else {
                $extensions = [];
            }
        }
    
        return $extensions;
    }
}