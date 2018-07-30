<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\PagesModule\Block\Form\Type\PageBlockType;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Manager\PageCollectionManager;

/**
 * Class PageBlock
 */
class PageBlock extends AbstractBlockHandler
{
    /**
     * Display block.
     *
     * @param array $properties
     *
     * @return string|void The rendered block
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('ZikulaPagesModule:pageblock:', "{$properties['title']}::", ACCESS_READ)) {
            return;
        }
        // return if no pid
        if (empty($properties['pid'])) {
            return;
        }

        // get the page
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        $page = $this->get('doctrine')->getRepository(PageEntity::class)->find($properties['pid']);
        // check for a valid item
        if (!$page) {
            return;
        }
        if (!$this->hasPermission('ZikulaPagesModule' . '::', "{$page->getTitle()}::{$page->getPageId()}", ACCESS_READ)) {
            return;
        }

        return $this->renderView('@ZikulaPagesModule/Block/pageBlockDisplay.html.twig', ['content' => $page->getContent()]);
    }

    public function getFormClassName()
    {
        return PageBlockType::class;
    }

    public function getFormTemplate()
    {
        return '@ZikulaPagesModule/Block/pageBlockModify.html.twig';
    }

    public function getFormOptions()
    {
        $pages = new PageCollectionManager($this->get('doctrine')->getManager());
        $choices = [];
        foreach ($pages->get() as $page) {
            $choices[$page->getPageid()] = $page->getTitle();
        }

        return [
            'pages' => $choices,
            'translator' => $this->getTranslator()
        ];
    }
}
