<?php

declare(strict_types=1);
/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class FormAwareHookSubscriber implements HookSubscriberInterface
{
    const SUBSCRIBER_FORMAWARE_TYPE_DISPLAY = 'zikulapagesmodule.form_aware_hook.pages.display';

    const SUBSCRIBER_FORMAWARE_TYPE_EDIT = 'zikulapagesmodule.form_aware_hook.pages.edit';

    const SUBSCRIBER_FORMAWARE_TYPE_PROCESS_EDIT = 'zikulapagesmodule.form_aware_hook.pages.process_edit';

    const SUBSCRIBER_FORMAWARE_TYPE_DELETE = 'zikulapagesmodule.form_aware_hook.pages.delete';

    const SUBSCRIBER_FORMAWARE_TYPE_PROCESS_DELETE = 'zikulapagesmodule.form_aware_hook.pages.process_delete';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOwner()
    {
        return 'ZikulaPagesModule';
    }

    public function getCategory()
    {
        return FormAwareCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('Pages FormAware hooks');
    }

    public function getEvents()
    {
        return [
            FormAwareCategory::TYPE_EDIT => self::SUBSCRIBER_FORMAWARE_TYPE_EDIT,
            FormAwareCategory::TYPE_PROCESS_EDIT => self::SUBSCRIBER_FORMAWARE_TYPE_PROCESS_EDIT,
            FormAwareCategory::TYPE_DELETE => self::SUBSCRIBER_FORMAWARE_TYPE_DELETE,
            FormAwareCategory::TYPE_PROCESS_DELETE => self::SUBSCRIBER_FORMAWARE_TYPE_PROCESS_DELETE
        ];
    }
}
