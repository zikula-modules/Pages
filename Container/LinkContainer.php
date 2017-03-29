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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApi $permissionApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $links = [];
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            if ($this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_READ)) {
                $links[] = [
                    'url' => $this->router->generate('zikulapagesmodule_admin_index'),
                    'text' => $this->translator->__('Pages list'),
                    'icon' => 'list'];
            }
            if ($this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_ADD)) {
                $links[] = [
                    'url' => $this->router->generate('zikulapagesmodule_adminform_edit'),
                    'text' => $this->translator->__('New Page'),
                    'icon' => 'plus'];
            }
            if ($this->permissionApi->hasPermission('ZikulaPagesModule::', '::', ACCESS_ADMIN)) {
                $links[] = [
                    'url' => $this->router->generate('zikulapagesmodule_admin_purge'),
                    'text' => $this->translator->__('Purge permalinks'),
                    'icon' => 'refresh'];
                $links[] = [
                    'url' => $this->router->generate('zikulapagesmodule_adminform_config'),
                    'text' => $this->translator->__('Modify Config'),
                    'icon' => 'wrench'];
            }
        }

        return $links;
    }

    public function getBundleName()
    {
        return 'ZikulaPagesModule';
    }
}
