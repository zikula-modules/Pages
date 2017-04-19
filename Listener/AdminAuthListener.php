<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Listener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Zikula\Core\Controller\AbstractController;
use Zikula\PagesModule\AdminAuthInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class AdminAuthListener
{
    private $permissionApi;

    /**
     * AdminAuthListener constructor.
     * @param PermissionApiInterface $permissionApi
     */
    public function __construct(PermissionApiInterface $permissionApi)
    {
        $this->permissionApi = $permissionApi;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AdminAuthInterface && $controller[0] instanceof AbstractController) {
            if (!$this->permissionApi->hasPermission($controller[0]->getName() . '::', '::', ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }
        }
    }
}
