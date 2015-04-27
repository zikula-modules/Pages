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

namespace Zikula\PagesModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\Entity\AbstractEntityCategory;

/**
 * Pages entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="pages_category",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
 */
class CategoryEntity extends AbstractEntityCategory
{
    /**
     * @ORM\ManyToOne(targetEntity="Zikula\PagesModule\Entity\PageEntity", inversedBy="categories")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="pageid")
     * @var \Zikula\PagesModule\Entity\PageEntity
     */
    private $entity;

    /**
     * Set entity
     *
     * @return \Zikula\PagesModule\Entity\PageEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * Set entity
     *
     * @param \Zikula\PagesModule\Entity\PageEntity $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

}