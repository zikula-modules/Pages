<?php
/**
 * Copyright Pages Team 2012
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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;

/**
 * Events entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="pages")
 */
class Pages_Entity_Pages extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the fid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pageid;
    
    /**
     * The following are annotations which define the title field.
     *
     * @ORM\Column(type="text")
     */
    private $title = '';

    /**
     * The following are annotations which define the metadescription field.
     *
     * @ORM\Column(type="text")
     */
    private $metadescription = '';

    /**
     * The following are annotations which define the metakeywords field.
     *
     * @ORM\Column(type="text")
     */
    private $metakeywords = '';


    /**
     * The following are annotations which define the urltitle field.
     *
     * @ORM\Column(type="text")
     */
    private $urltitle = '';


    /**
     * The following are annotations which define the content field.
     *
     * @ORM\Column(type="text")
     */
    private $content = '';

    /**
     * The following are annotations which define the counter field.
     *
     * @ORM\Column(type="integer")
     */
    private $counter = 0;

    /**
     * The following are annotations which define the displaywrapper field.
     *
     * @ORM\Column(type="boolean")
     */
    private $displaywrapper = true;

    /**
     * The following are annotations which define the displaytitle field.
     *
     * @ORM\Column(type="boolean")
     */
    private $displaytitle = true;

    /**
     * The following are annotations which define the displaycreated field.
     *
     * @ORM\Column(type="boolean")
     */
    private $displaycreated = true;

    /**
     * The following are annotations which define the displayupdated field.
     *
     * @ORM\Column(type="boolean")
     */
    private $displayupdated = true;

    /**
     * The following are annotations which define the displaytextinfo field.
     *
     * @ORM\Column(type="boolean")
     */
    private $displaytextinfo = true;


    /**
     * The following are annotations which define the displayprint field.
     *
     * @ORM\Column(type="boolean")
     */
    private $displayprint = true;


    /**
     * The following are annotations which define the language field.
     *
     * @ORM\Column(type="string", length="30")
     */
    private $language = '';


    public function getPageid()
    {
        return $this->pageid;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getMetadescription()
    {
        return $this->metadescription;
    }


    public function getMetakeywords()
    {
        return $this->metakeywords;
    }

    public function getUrltitle()
    {
        return $this->urltitle;
    }


    public function getContent()
    {
        return $this->content;
    }

    public function getCounter()
    {
        return $this->counter;
    }

    public function getDisplaywrapper()
    {
        return $this->displaywrapper;
    }

    public function getDisplaytitle()
    {
        return $this->displaytitle;
    }

    public function getDisplaycreated()
    {
        return $this->displaycreated;
    }



    public function getdisplayupdated()
    {
        return $this->displayupdated;
    }

    public function getdisplaytextinfo()
    {
        return $this->displaytextinfo;
    }

    public function getdisplayprint()
    {
        return $this->displayprint;
    }

    public function getLanguage()
    {
        return $this->language;
    }



    public function setpageid($pageid)
    {
        $this->pageid = $pageid;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setMetadescription($metadescription)
    {
        $this->metadescription = $metadescription;
    }


    public function setMetakeywords($metakeywords)
    {
        $this->metakeywords = $metakeywords;
    }

    public function setUrltitle($urltitle)
    {
        $this->urltitle = $urltitle;
    }


    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setCounter($counter)
    {
        $this->counter = $counter;
    }

    public function setDisplaywrapper($displaywrapper)
    {
        $this->displaywrapper = $displaywrapper;
    }

    public function setDisplaytitle($displaytitle)
    {
        $this->displaytitle = $displaytitle;
    }

    public function setDisplaycreated($displaycreated)
    {
        $this->displaycreated = $displaycreated;
    }


    public function setDisplayupdated($displayupdated)
    {
        $this->displayupdated = $displayupdated;
    }

    public function setDisplaytextinfo($displaytextinfo)
    {
        $this->displaytextinfo = $displaytextinfo;
    }

    public function setDisplayprint($displayprint)
    {
        $this->displayprint = $displayprint;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }




    public function incrementCounter()
    {
        $this->counter++;
    }


    /**
     * The following are annotations which define the id field.
     *
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     */
    private $cr_uid;

    public function getCr_uid() {
        return $this->cr_uid;
    }

    /**
     * The following are annotations which define the id field.
     *
     * @var datetime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $cr_date;

    public function getCr_date() {
        return $this->cr_date;
    }


    /**
     * The following are annotations which define the id field.
     *
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     */
    private $lu_uid;

    public function getLu_uid() {
        return $this->lu_uid;
    }

    /**
     * The following are annotations which define the id field.
     *
     * @var datetime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $lu_date;

    public function getLu_date() {
        return $this->lu_date;
    }


    /**
     * @ORM\OneToMany(targetEntity="Pages_Entity_Categories",
     *                mappedBy="entity", cascade={"all"},
     *                orphanRemoval=true, indexBy="categoryRegistryId")
     */
    private $categories;

    public function getCategories() {
        return $this->categories;
    }

    public function setCategories($categories) {
        $this->categories = $categories;
    }

    public function getCategories2() {
        $output = array();
        foreach ($this->categories as $category) {
            $test = $category->toArray();
            $output[] = $test['category']['name'];
        }
        return implode(', ', $output);
    }


    /**
     * The following are annotations which define the counter field.
     *
     * @ORM\Column(type="string", length=1)
     */
    private $obj_status = 'A';

    public function getObj_status() {
        return $this->obj_status;
    }


    public function __construct()
    {
        $this->categories = new Doctrine\Common\Collections\ArrayCollection();
    }


}