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

namespace Zikula\PagesModule\Menu\Base;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PagesModule\Helper\ControllerHelper;
use Zikula\PagesModule\Helper\PermissionHelper;

/**
 * This is the extension menu service base class.
 */
abstract class AbstractExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var ControllerHelper
     */
    protected $controllerHelper;

    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;

    public function __construct(
        FactoryInterface $factory,
        ControllerHelper $controllerHelper,
        PermissionHelper $permissionHelper
    ) {
        $this->factory = $factory;
        $this->controllerHelper = $controllerHelper;
        $this->permissionHelper = $permissionHelper;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        $contextArgs = ['api' => 'extensionMenu', 'action' => 'get'];
        $allowedObjectTypes = $this->controllerHelper->getObjectTypes('api', $contextArgs);

        $permLevel = self::TYPE_ADMIN === $type ? ACCESS_ADMIN : ACCESS_READ;

        $menu = $this->factory->createItem('zikulapagesmodule' . ucfirst($type) . 'Menu');

        if (self::TYPE_ACCOUNT === $type) {
            return 0 === $menu->count() ? null : $menu;
        }

        $routeArea = self::TYPE_ADMIN === $type ? 'admin' : '';
        if (self::TYPE_ADMIN === $type) {
            if ($this->permissionHelper->hasPermission(ACCESS_READ)) {
                $menu->addChild('Frontend', [
                    'route' => 'zikulapagesmodule_page_index',
                ])
                    ->setAttribute('icon', 'fas fa-home')
                    ->setLinkAttribute('title', 'Switch to user area.')
                ;
            }
        } else {
            if ($this->permissionHelper->hasPermission(ACCESS_ADMIN)) {
                $menu->addChild('Backend', [
                    'route' => 'zikulapagesmodule_page_adminindex',
                ])
                    ->setAttribute('icon', 'fas fa-wrench')
                    ->setLinkAttribute('title', 'Switch to administration area.')
                ;
            }
        }
        
        if (
            in_array('page', $allowedObjectTypes, true)
            && $this->permissionHelper->hasComponentPermission('page', $permLevel)
        ) {
            $menu->addChild('Pages', [
                'route' => 'zikulapagesmodule_page_' . $routeArea . 'view'
            ])
                ->setLinkAttribute('title', 'Pages list')
                ->setExtra('translation_domain', 'page')
            ;
        }
        if ('admin' === $routeArea && $this->permissionHelper->hasPermission(ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulapagesmodule_config_config',
            ])
                ->setAttribute('icon', 'fas fa-wrench')
                ->setLinkAttribute('title', 'Manage settings for this application')
                ->setExtra('translation_domain', 'config')
            ;
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaPagesModule';
    }
}
