<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\PagesModule\Block;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\AbstractBlockHandler;
use Zikula\PagesModule\Manager\PageCollectionManager;

/**
 * Class PageBlock
 * @package Zikula\PagesModule\Block
 */
class PageBlock extends AbstractBlockHandler
{
    /**
     * Display block.
     *
     * @param array $blockInfo A blockInfo structure.
     *
     * @return string|void The rendered block.
     */
    public function display(array $properties)
    {
        // Security check
        if (!$this->hasPermission('ZikulaPagesModule:pageblock:', "{$properties['title']}::", ACCESS_READ)) {
            return false;
        }
        // return if no pid
        if (empty($properties['pid'])) {
            return false;
        }

        // get the page
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        $page = $this->get('doctrine.entitymanager')->getRepository('\Zikula\PagesModule\Entity\PageEntity')->find($properties['pid']);
        // check for a valid item
        if (!$page) {
            return false;
        }
        if (!$this->hasPermission('ZikulaPagesModule' . '::', "{$page->getTitle()}::{$page->getPageId()}", ACCESS_READ)) {
            return false;
        }

        return $this->renderView('ZikulaPagesModule:Block:pageBlockDisplay.html.twig', array('content' => $page->getContent()));
    }

    /**
     * modify block settings
     *
     * @param Request $request
     * @param array $properties
     * @return string the block form
     */
    public function modify(Request $request, array $properties)
    {
        $defaults = [
            'pid' => '',
        ];
        $vars = array_merge($defaults, $properties);
        $pages = new PageCollectionManager($this->get('doctrine.entitymanager'));
        $choices = [];
        foreach ($pages->get() as $page) {
            $choices[$page->getPageid()] = $page->getTitle();
        }
        $options = ['pages' => $choices];
        $form = $this->createForm('Zikula\PagesModule\Block\Form\Type\PageBlockType', $vars, $options);
        $form->handleRequest($request);
        if ($form->isValid()) {

            return $form->getData();
        }

        return $this->renderView('ZikulaPagesModule:Block:pageBlockModify.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}