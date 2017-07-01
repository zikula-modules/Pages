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

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;
use Zikula\Bundle\HookBundle\Category\FilterHooksCategory;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;

class HookContainer extends AbstractHookContainer
{
    const SUBSCRIBER_UIHOOKS_AREANAME = 'subscriber.pages.ui_hooks.pages';
    const SUBSCRIBER_UIFILTERHOOKS_AREANAME = 'subscriber.pages.filter_hooks.pagesfilter';

    const SUBSCRIBER_FORMAWARE_AREANAME = 'subscriber.pages.form_aware_hook.pages'; // <type>.<name>.<category>.<area>
    const SUBSCRIBER_FORMAWARE_TYPE_DISPLAY = 'zikulapagesmodule.form_aware_hook.pages.display'; // <module>.<category>.<area>.<type>
    const SUBSCRIBER_FORMAWARE_TYPE_EDIT = 'zikulapagesmodule.form_aware_hook.pages.edit';
    const SUBSCRIBER_FORMAWARE_TYPE_PROCESS_EDIT = 'zikulapagesmodule.form_aware_hook.pages.process_edit';
    const SUBSCRIBER_FORMAWARE_TYPE_DELETE = 'zikulapagesmodule.form_aware_hook.pages.delete';
    const SUBSCRIBER_FORMAWARE_TYPE_PROCESS_DELETE = 'zikulapagesmodule.form_aware_hook.pages.process_delete';

    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaPagesModule', self::SUBSCRIBER_UIHOOKS_AREANAME, UiHooksCategory::NAME, $this->__('Pages Hooks'));
        $bundle->addEvent(UiHooksCategory::TYPE_DISPLAY_VIEW, 'pages.ui_hooks.pages.display_view');
        $bundle->addEvent(UiHooksCategory::TYPE_FORM_EDIT, 'pages.ui_hooks.pages.form_edit');
        $bundle->addEvent(UiHooksCategory::TYPE_FORM_DELETE, 'pages.ui_hooks.pages.form_delete');
        $bundle->addEvent(UiHooksCategory::TYPE_VALIDATE_EDIT, 'pages.ui_hooks.pages.validate_edit');
        $bundle->addEvent(UiHooksCategory::TYPE_VALIDATE_DELETE, 'pages.ui_hooks.pages.validate_delete');
        $bundle->addEvent(UiHooksCategory::TYPE_PROCESS_EDIT, 'pages.ui_hooks.pages.process_edit');
        $bundle->addEvent(UiHooksCategory::TYPE_PROCESS_DELETE, 'pages.ui_hooks.pages.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new SubscriberBundle('ZikulaPagesModule', self::SUBSCRIBER_UIFILTERHOOKS_AREANAME, FilterHooksCategory::NAME, $this->__('Pages Filter Hooks'));
        $bundle->addEvent(FilterHooksCategory::TYPE_FILTER, 'pages.filter_hooks.pages.filter');
        $this->registerHookSubscriberBundle($bundle);

        /**
         * new FormAware Subscriber
         */
        $bundle = new SubscriberBundle('ZikulaPagesModule', self::SUBSCRIBER_FORMAWARE_AREANAME, FormAwareCategory::NAME, $this->__('Pages FormAware Subscribers'));
        $bundle->addEvent(FormAwareCategory::TYPE_EDIT, self::SUBSCRIBER_FORMAWARE_TYPE_EDIT);
        $bundle->addEvent(FormAwareCategory::TYPE_PROCESS_EDIT, self::SUBSCRIBER_FORMAWARE_TYPE_PROCESS_EDIT);
        $bundle->addEvent(FormAwareCategory::TYPE_DELETE, self::SUBSCRIBER_FORMAWARE_TYPE_DELETE);
        $bundle->addEvent(FormAwareCategory::TYPE_PROCESS_DELETE, self::SUBSCRIBER_FORMAWARE_TYPE_PROCESS_DELETE);
        $this->registerHookSubscriberBundle($bundle);
    }
}
