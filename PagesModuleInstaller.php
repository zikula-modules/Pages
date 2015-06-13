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

namespace Zikula\PagesModule;

use CategoryRegistryUtil;
use CategoryUtil;
use DoctrineHelper;
use HookUtil;
use Zikula\PagesModule\Entity\CategoryEntity;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula_Exception;
use ZLanguage;

/**
 * Provides module installation and upgrade services.
 */
class PagesModuleInstaller extends \Zikula_AbstractInstaller
{
    private $entities = array(
        'Zikula\PagesModule\Entity\PageEntity',
        'Zikula\PagesModule\Entity\CategoryEntity'
    );

    /**
     * initialise the module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean
     */
    public function install()
    {
        // create table
        try {
            DoctrineHelper::createSchema($this->entityManager, $this->entities);
        } catch (\Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }
        // insert default category
        try {
            $this->createCategoryTree();
        } catch (\Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__f('Did not create default categories (%s).', $e->getMessage()));
        }
        // set up config variables
        $modvars = array(
            'itemsperpage' => 25,
            'enablecategorization' => true,
            'addcategorytitletopermalink' => true,
            'showpermalinkinput' => true,
            'def_displaywrapper' => true,
            'def_displaytitle' => true,
            'def_displaycreated' => true,
            'def_displayupdated' => true,
            'def_displaytextinfo' => true,
            'def_displayprint' => true
        );
        $this->setVars($modvars);
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        $this->createIntroPage();
        // initialisation successful
        return true;
    }

    /**
     * Upgrade the errors module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldversion Version number string to upgrade from.
     *
     * @return mixed True on success, last valid version string or false if fails.
     */
    public function upgrade($oldversion)
    {
        // Only support upgrade from version 2.5.1 and up. Notify users if they have a version below that one.
        if (version_compare($oldversion, '2.5.1', '<=')) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Notice: This version does not support upgrades from versions of Pages less than 2.5.1. Please upgrade to 2.5.1 before attempting this upgrade.'));
            return false;
        }
        switch ($oldversion) {
            case '2.5.1':
                // create categories table
                try {
                    DoctrineHelper::createSchema($this->entityManager, array('Zikula\PagesModule\Entity\CategoryEntity'));
                } catch (\Exception $e) {
                    $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
                    return false;
                }
                // move relations from categories_mapobj to pages_category
                // then delete old data
                $connection = $this->entityManager->getConnection();
                $sqls = array();
                $sqls[] = 'INSERT INTO pages_category (entityId, registryId, categoryId) SELECT obj_id, reg_id, category_id FROM categories_mapobj WHERE modname = \'Pages\' AND tablename = \'pages\'';
                $sqls[] = 'DELETE FROM categories_mapobj WHERE modname = \'Pages\' AND tablename = \'pages\'';
                // update category registry data to change tablename to EntityName
                $sqls[] = 'UPDATE categories_registry SET tablename = \'Pages\' WHERE tablename = \'pages\'';
                // do changes
                foreach ($sqls as $sql) {
                    $stmt = $connection->prepare($sql);
                    try {
                        $stmt->execute();
                    } catch (\Exception $e) {
                        $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
                    }
                }
            case '2.6.0':
            case '2.6.1':
                // @todo convert modvar module name
                // @todo convert category paths to ZikulaPagesModule
                // @todo convert categoryRegistry Entity name from Page to PageEntity
                // @todo convert security schema names to ZikulaPagesModule
            case '3.0.0':
        }
        // Update successful
        return true;
    }

    /**
     * delete the errors module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean
     */
    public function uninstall()
    {
        // drop table
        DoctrineHelper::dropSchema($this->entityManager, $this->entities);
        // Delete any module variables
        $this->delVars();
        // Delete entries from category registry
        CategoryRegistryUtil::deleteEntry($this->name);
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());
        // Deletion successful
        return true;
    }

    /**
     * create the category tree
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If Root category not found.
     * @throws \Zikula_Exception
     *
     * @return boolean
     */
    private function createCategoryTree()
    {
        // create category
        CategoryUtil::createCategory('/__SYSTEM__/Modules', $this->name, null, $this->__('Pages'), $this->__('Static pages'));
        // create subcategory
        CategoryUtil::createCategory('/__SYSTEM__/Modules/ZikulaPagesModule', 'Category1', null, $this->__('Category 1'), $this->__('Initial sub-category created on install'), array('color' => '#99ccff'));
        CategoryUtil::createCategory('/__SYSTEM__/Modules/ZikulaPagesModule', 'Category2', null, $this->__('Category 2'), $this->__('Initial sub-category created on install'), array('color' => '#cceecc'));
        // get the category path to insert Pages categories
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/ZikulaPagesModule');
        if ($rootcat) {
            // create an entry in the categories registry to the Main property
            if (!CategoryRegistryUtil::insertEntry($this->name, 'PageEntity', 'Main', $rootcat['id'])) {
                throw new Zikula_Exception('Cannot insert Category Registry entry.');
            }
        } else {
            $this->throwNotFound('Root category not found.');
        }
        return true;
    }

    /**
     * create a sample page
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function createIntroPage()
    {
        $content = $this->__(/** @Ignore */
            'This is a demonstration page. You can use Pages to create simple static content pages. It is excellent '
            . 'if you only need basic html for your pages. You can also utilize the Scribite module for WYSIWYG '
            . 'content creation. It is well suited for informational articles, documents and other "long term" type '
            . 'content items.' . '<br /><br />'
            . 'Pages is a hookable module which allows you to hook EZComments or other hook providers to extend the '
            . 'capabilities of your module.'
        );
        $data = array(
            'title' => $this->__('Welcome to Pages content manager'),
            'urltitle' => $this->__('welcome-to-pages-content-manager'),
            'content' => $content,
            'language' => ZLanguage::getLanguageCode());
        $page = new PageEntity();
        $page->merge($data);
        $this->entityManager->persist($page);
        $category = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/ZikulaPagesModule/Category1');
        $catEntity = $this->entityManager->getReference('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $category['id']);
        $page->setCategories(array($catEntity));
        $this->entityManager->flush();
    }

}