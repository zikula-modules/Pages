<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @see https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Zikula\PagesModule\Listener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Zikula\Core\Controller\AbstractController;
use Zikula\PagesModule\AdminAuthInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class AdminAuthListener
{
    private $permissionApi;

    /**
     * AdminAuthListener constructor.
     * @param PermissionApi $permissionApi
     */
    public function __construct(PermissionApi $permissionApi)
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
