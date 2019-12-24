<?php

declare(strict_types=1);
/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * Pages entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="pages_category",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
 */
class CategoryAssignmentEntity extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="Zikula\PagesModule\Entity\PageEntity", inversedBy="categoryAssignments")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="pageid")
     * @var \Zikula\PagesModule\Entity\PageEntity
     */
    private $entity;

    /**
     * Get entity
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
