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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\PagesModule\Entity\CategoryEntity as PagesCategoryRelation;

/**
 * Page entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="pages")
 */
class PageEntity extends \Zikula\Core\Doctrine\EntityAccess
{
    /**
     * pageid
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pageid;
    /**
     * title
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $title = '';
    /**
     * metadescription
     *
     * @ORM\Column(type="text")
     */
    private $metadescription = '';
    /**
     * metakeywords
     *
     * @ORM\Column(type="text")
     */
    private $metakeywords = '';
    /**
     * urltitle
     *
     * @ORM\Column(type="text")
     * @Gedmo\Slug(fields={"title"})
     */
    private $urltitle = '';
    /**
     * content
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content = '';
    /**
     * counter
     *
     * @ORM\Column(type="integer")
     */
    private $counter = 0;
    /**
     * displaywrapper
     *
     * @ORM\Column(type="boolean")
     */
    private $displaywrapper = true;
    /**
     * displaytitle
     *
     * @ORM\Column(type="boolean")
     */
    private $displaytitle = true;
    /**
     * displaycreated
     *
     * @ORM\Column(type="boolean")
     */
    private $displaycreated = true;
    /**
     * displayupdated
     *
     * @ORM\Column(type="boolean")
     */
    private $displayupdated = true;
    /**
     * displaytextinfo
     *
     * @ORM\Column(type="boolean")
     */
    private $displaytextinfo = true;
    /**
     * displayprint
     *
     * @ORM\Column(type="boolean")
     */
    private $displayprint = true;
    /**
     * language
     *
     * @ORM\Column(type="string", length=30)
     */
    private $language = '';
    /**
     * cr_uid
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="cr_uid", referencedColumnName="uid")
     */
    private $creator;
    /**
     * cr_date
     *
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $cr_date;
    /**
     * lu_uid
     *
     * @Gedmo\Blameable(on="create")
     * @Gedmo\Blameable(on="change", field={"title", "metadescription", "metakeywords", "content"})
     * @ORM\ManyToOne(targetEntity="Zikula\Module\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="lu_uid", referencedColumnName="uid")
     */
    private $updater;
    /**
     * lu_date
     *
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Gedmo\Timestampable(on="change", field={"title", "metadescription", "metakeywords", "content"})
     */
    private $lu_date;
    /**
     * categories
     *
     * @ORM\OneToMany(targetEntity="Zikula\PagesModule\Entity\CategoryEntity",
     *                mappedBy="entity", cascade={"remove", "persist"},
     *                orphanRemoval=true, fetch="EAGER")
     */
    private $categories;
    /**
     * obj_status
     *
     * @ORM\Column(type="string", length=1)
     */
    private $obj_status = 'A';

    /**
     * Constuctor
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $modVars = \ModUtil::getVar('ZikulaPagesModule');
        $this->displaywrapper = $modVars['def_displaywrapper'];
        $this->displaytitle = $modVars['def_displaytitle'];
        $this->displaycreated = $modVars['def_displaycreated'];
        $this->displayupdated = $modVars['def_displayupdated'];
        $this->displaytextinfo = $modVars['def_displaytextinfo'];
        $this->displayprint = $modVars['def_displayprint'];
    }

    /**
     * get pageid
     *
     * @return integer
     */
    public function getPageid()
    {

        return $this->pageid;
    }

    /**
     * 'fake' setter so formcategoryselector doesn't blow up
     *
     * @param $pageid
     */
    public function setPageid($pageid)
    {

    }

    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {

        return $this->title;
    }

    /**
     * Set page title
     *
     * @param string $title
     */
    public function setTitle($title)
    {

        $this->title = $title;
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetadescription()
    {

        return $this->metadescription;
    }

    /**
     * Set page meta description
     *
     * @param string $metadescription
     */
    public function setMetadescription($metadescription)
    {

        $this->metadescription = $metadescription;
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetakeywords()
    {

        return $this->metakeywords;
    }

    /**
     * Set page meta keywords
     *
     * @param string $metakeywords
     */
    public function setMetakeywords($metakeywords)
    {

        $this->metakeywords = $metakeywords;
    }

    /**
     * Get url title
     *
     * @return string
     */
    public function getUrltitle()
    {

        return $this->urltitle;
    }

    /**
     * Set page url title
     *
     * @param string $urltitle
     */
    public function setUrltitle($urltitle)
    {

        $this->urltitle = $urltitle;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {

        return $this->content;
    }

    /**
     * Set page content
     *
     * @param string $content
     */
    public function setContent($content)
    {

        $this->content = $content;
    }

    /**
     * Get counter
     *
     * @return int
     */
    public function getCounter()
    {

        return $this->counter;
    }

    /**
     * Set page counter
     *
     * @param int $counter
     */
    public function setCounter($counter)
    {

        $this->counter = $counter;
    }

    /**
     * Get display wrapper
     *
     * @return bool
     */
    public function getDisplaywrapper()
    {

        return $this->displaywrapper;
    }

    /**
     * Set page display wrapper
     *
     * @param bool $displaywrapper
     */
    public function setDisplaywrapper($displaywrapper)
    {

        $this->displaywrapper = $displaywrapper;
    }

    /**
     * Get display title
     *
     * @return bool
     */
    public function getDisplaytitle()
    {

        return $this->displaytitle;
    }

    /**
     * Set if title should be shown
     *
     * @param bool $displaytitle
     */
    public function setDisplaytitle($displaytitle)
    {

        $this->displaytitle = $displaytitle;
    }

    /**
     * Get display created
     *
     * @return bool
     */
    public function getDisplaycreated()
    {

        return $this->displaycreated;
    }

    /**
     * Set if the creator name and the creation time should be shown.
     *
     * @param bool $displaycreated
     */
    public function setDisplaycreated($displaycreated)
    {

        $this->displaycreated = $displaycreated;
    }

    /**
     * Get display updated
     *
     * @return bool
     */
    public function getdisplayupdated()
    {

        return $this->displayupdated;
    }

    /**
     * Get display text info
     *
     * @return bool
     */
    public function getdisplaytextinfo()
    {

        return $this->displaytextinfo;
    }

    /**
     * Get display print
     *
     * @return bool
     */
    public function getdisplayprint()
    {

        return $this->displayprint;
    }

    /**
     * Get page language
     *
     * @return string
     */
    public function getLanguage()
    {

        return $this->language;
    }

    /**
     * Set page language
     *
     * @param $language
     */
    public function setLanguage($language)
    {

        $this->language = $language;
    }

    /**
     * Set if the updater name and the update time should be shown.
     *
     * @param bool $displayupdated
     */
    public function setDisplayupdated($displayupdated)
    {

        $this->displayupdated = $displayupdated;
    }

    /**
     * Set if the text info should be shown.
     *
     * @param bool $displaytextinfo
     */
    public function setDisplaytextinfo($displaytextinfo)
    {

        $this->displaytextinfo = $displaytextinfo;
    }

    /**
     * Set if print link should shown
     *
     * @param bool $displayprint
     */
    public function setDisplayprint($displayprint)
    {

        $this->displayprint = $displayprint;
    }

    /**
     * Increment page counter
     */
    public function incrementCounter()
    {

        $this->counter++;
    }

    /**
     * Get creation time
     *
     * @return \DateTime
     */
    public function getCr_date()
    {

        return $this->cr_date;
    }

    /**
     * Get last update date
     *
     * @return \DateTime
     */
    public function getLu_date()
    {

        return $this->lu_date;
    }

    /**
     * Get page categories
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set page categories
     *
     * @param $categories
     */
    public function setCategories(ArrayCollection $categories)
    {
        foreach ($this->categories as $categoryAssignment) {
            if (false === $key = $this->collectionContains($categories, $categoryAssignment)) {
                $this->categories->removeElement($categoryAssignment);
            } else {
                $categories->remove($key);
            }
        }
        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }

    /**
     * Check if a collection contains an element based only on two criteria (categoryRegistryId, categoy).
     * @param $collection
     * @param $element
     * @return bool|int
     */
    private function collectionContains($collection, $element)
    {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var \Zikula\PagesModule\Entity\CategoryEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() == $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() == $element->getCategory()
            ) {

                return $key;
            }
        }

        return false;
    }

    /**
     * Get object status
     *
     * @return string
     */
    public function getObj_status()
    {

        return $this->obj_status;
    }

    public function getObjStatus()
    {
        return $this->obj_status == 'A';
    }

    public function setObjStatus($status)
    {
        $this->obj_status = $status ? 'A' : 'I';
    }
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return \Zikula\UsersModule\Entity\UserEntity
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return mixed
     */
    public function getUpdater()
    {
        return $this->updater;
    }

    /**
     * @param mixed $updater
     */
    public function setUpdater($updater)
    {
        $this->updater = $updater;
    }

}