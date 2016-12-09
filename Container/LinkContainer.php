<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\Translator;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var Translator
     */
    private $translator;
    /**
     * @var RouterInterface
     */
    private $router;
    private $permissionApi;

    public function __construct($translator, RouterInterface $router, PermissionApi $permissionApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $links = array();
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            if ($this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_READ)) {
                $links[] = array(
                    'url' => $this->router->generate('zikulapagesmodule_admin_index'),
                    'text' => $this->translator->__('Pages list'),
                    'icon' => 'list');
            }
            if ($this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_ADD)) {
                $links[] = array(
                    'url' => $this->router->generate('zikulapagesmodule_adminform_edit'),
                    'text' => $this->translator->__('New Page'),
                    'icon' => 'plus');
            }
            if ($this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_ADMIN)) {
                $links[] = array(
                    'url' => $this->router->generate('zikulapagesmodule_admin_purge'),
                    'text' => $this->translator->__('Purge permalinks'),
                    'icon' => 'refresh');
                $links[] = array(
                    'url' => $this->router->generate('zikulapagesmodule_adminform_config'),
                    'text' => $this->translator->__('Modify Config'),
                    'icon' => 'wrench');
            }
        }

        return $links;
    }

    public function getBundleName()
    {
        return 'ZikulaPagesModule';
    }
}
