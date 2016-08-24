<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Zikula\PagesModule\Container;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;

class HookContainer extends AbstractHookContainer
{
    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaPagesModule', 'subscriber.pages.ui_hooks.pages', 'ui_hooks', $this->__('Pages Hooks'));
        $bundle->addEvent('display_view', 'pages.ui_hooks.pages.display_view');
        $bundle->addEvent('form_edit', 'pages.ui_hooks.pages.form_edit');
        $bundle->addEvent('form_delete', 'pages.ui_hooks.pages.form_delete');
        $bundle->addEvent('validate_edit', 'pages.ui_hooks.pages.validate_edit');
        $bundle->addEvent('validate_delete', 'pages.ui_hooks.pages.validate_delete');
        $bundle->addEvent('process_edit', 'pages.ui_hooks.pages.process_edit');
        $bundle->addEvent('process_delete', 'pages.ui_hooks.pages.process_delete');
        $this->registerHookSubscriberBundle($bundle);
        $bundle = new SubscriberBundle('ZikulaPagesModule', 'subscriber.pages.filter_hooks.pagesfilter', 'filter_hooks', $this->__('Pages Filter Hooks'));
        $bundle->addEvent('filter', 'pages.filter_hooks.pages.filter');
        $this->registerHookSubscriberBundle($bundle);
    }
}
