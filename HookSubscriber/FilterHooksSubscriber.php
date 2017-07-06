<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\FilterHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class FilterHooksSubscriber implements HookSubscriberInterface
{
    const FILTER = 'pages.filter_hooks.pages.filter';

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
        return FilterHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('Pages filter hooks');
    }

    public function getEvents()
    {
        return [
            FilterHooksCategory::TYPE_FILTER => self::FILTER,
        ];
    }
}
