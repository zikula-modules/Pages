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

namespace Zikula\PagesModule\TaggedObjectMeta;

use DateUtil;

/**
 * Tagged object meta class.
 */
class PagesTaggedObjectMeta extends \Tag_AbstractTaggedObjectMeta
{
    /**
     * Construct.
     *
     * @param int $objectId Object ID.
     * @param int $areaId A blockinfo structure.
     * @param string $module Module.
     * @param string $urlString Url.
     * @param \Zikula_ModUrl $urlObject Url object.
     */
    public function __construct(
        $objectId,
        $areaId,
        $module,
        $urlString = null,
        \Zikula_ModUrl $urlObject = null
    ) {
        parent::__construct($objectId, $areaId, $module, $urlString, $urlObject);
        $sm = \ServiceUtil::getManager();
        /** @var \Zikula\PagesModule\Entity\PageEntity $page */
        $page = $sm->get('doctrine.entitymanager')->getRepository('ZikulaPagesModule:PageEntity')->find($this->getObjectId());
        // the Api checks for perms and there is nothing else to check
        if ($page) {
            $this->setObjectAuthor($page->getCreator()->getUname());
            $this->setObjectDate($page->getCr_date());
            $this->setObjectTitle($page->getTitle());
        }
    }

    /**
     * Set object title.
     *
     * @param string $title Object title.
     */
    public function setObjectTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set object date.
     *
     * @param string $date Date.
     */
    public function setObjectDate($date)
    {
        $this->date = DateUtil::formatDatetime($date, 'datetimebrief');
    }

    /**
     * Set object author.
     *
     * @param string $author Object author.
     */
    public function setObjectAuthor($author)
    {
        $this->author = $author;
    }
}
