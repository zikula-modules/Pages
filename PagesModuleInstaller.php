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
use Doctrine\Common\Collections\ArrayCollection;
use HookUtil;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Core\AbstractBundle;
use Zikula\PagesModule\Entity\CategoryEntity;
use Zikula\PagesModule\Entity\PageEntity;
use ZLanguage;
use Zikula\Core\ExtensionInstallerInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides module installation and upgrade services.
 */
class PagesModuleInstaller implements ExtensionInstallerInterface, ContainerAwareInterface
{
    use TranslatorTrait;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var AbstractBundle
     */
    private $bundle;
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
            $this->container->get('zikula.doctrine.schema_tool')->create($this->entities);
        } catch (\Exception $e) {
            $this->container->get('request')->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }
        // insert default category
        try {
            $this->createCategoryTree();
        } catch (\Exception $e) {
            $this->container->get('request')->getSession()->getFlashBag()->add('error', $this->__f('Did not create default categories (%s).', $e->getMessage()));
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
        \ModUtil::setVars($this->bundle->getName(), $modvars);
        $versionClass = $this->bundle->getVersionClass();
        $version = new $versionClass($this->bundle);
        HookUtil::registerSubscriberBundles($version->getHookSubscriberBundles());
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
            $this->container->get('request')->getSession()->getFlashBag()->add('error', $this->__('Notice: This version does not support upgrades from versions of Pages less than 2.5.1. Please upgrade to 2.5.1 before attempting this upgrade.'));
            return false;
        }
        $connection = $this->container->get('doctrine.entitymanager')->getConnection();

        switch ($oldversion) {
            case '2.5.1':
                // create categories table
                try {
                    $this->container->get('zikula.doctrine.schema_tool')->create(array('Zikula\PagesModule\Entity\CategoryEntity'));
                } catch (\Exception $e) {
                    $this->container->get('request')->getSession()->getFlashBag()->add('error', $e->getMessage());
                    return false;
                }
                // move relations from categories_mapobj to pages_category
                // then delete old data
                $sqls = array();
                $sqls[] = 'INSERT INTO pages_category (entityId, registryId, categoryId) SELECT obj_id, reg_id, category_id FROM categories_mapobj WHERE modname = \'Pages\' AND tablename = \'pages\'';
                $sqls[] = 'DELETE FROM categories_mapobj WHERE modname = \'Pages\' AND tablename = \'pages\'';
                // update category registry data to change tablename to EntityName
                $sqls[] = 'UPDATE categories_registry SET entityname = \'Pages\' WHERE entityname = \'pages\'';
                // do changes
                foreach ($sqls as $sql) {
                    $stmt = $connection->prepare($sql);
                    try {
                        $stmt->execute();
                    } catch (\Exception $e) {
                        $this->container->get('request')->getSession()->getFlashBag()->add('error', $e->getMessage());
                    }
                }
            case '2.6.0':
            case '2.6.1':
                $sqls = [];
                // convert modvar module name
                $sqls[] = "UPDATE module_vars SET modname = 'ZikulaPagesModule' WHERE modname = 'Pages'";

                // convert categoryRegistry Entity name from Page to PageEntity
                $sqls[] = 'UPDATE categories_registry SET entityname = \'PageEntity\' WHERE entityname = \'Pages\'';
                $sqls[] = 'UPDATE categories_registry SET modname = \'ZikulaPagesModule\' WHERE modname = \'Pages\'';

                // convert security schema names to ZikulaPagesModule
                $sqls[] = "UPDATE group_perms SET component = CONCAT('ZikulaPagesModule', SUBSTRING(component, 6)) WHERE component LIKE 'Pages%'";

                foreach ($sqls as $sql) {
                    $stmt = $connection->prepare($sql);
                    try {
                        $stmt->execute();
                    } catch (\Exception $e) {
                        $this->container->get('request')->getSession()->getFlashBag()->add('error', $e->getMessage());
                    }
                }
            case '3.0.0':
            case '3.0.1':
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
        $this->container->get('zikula.doctrine.schema_tool')->drop($this->entities);
        // Delete any module variables
        \ModUtil::delVar($this->bundle->getName());
        // Delete entries from category registry
        CategoryRegistryUtil::deleteEntry($this->bundle->getName());
        $versionClass = $this->bundle->getVersionClass();
        $version = new $versionClass($this->bundle);
        HookUtil::unregisterSubscriberBundles($version->getHookSubscriberBundles());
        // Deletion successful
        return true;
    }

    /**
     * create the category tree
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If Root category not found.
     * @throws \Exception
     *
     * @return boolean
     */
    private function createCategoryTree()
    {
        // create category
        CategoryUtil::createCategory('/__SYSTEM__/Modules', $this->bundle->getName(), null, $this->__('Pages'), $this->__('Static pages'));
        // create subcategory
        CategoryUtil::createCategory('/__SYSTEM__/Modules/ZikulaPagesModule', 'Category1', null, $this->__('Category 1'), $this->__('Initial sub-category created on install'), array('color' => '#99ccff'));
        CategoryUtil::createCategory('/__SYSTEM__/Modules/ZikulaPagesModule', 'Category2', null, $this->__('Category 2'), $this->__('Initial sub-category created on install'), array('color' => '#cceecc'));
        // get the category path to insert Pages categories
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/ZikulaPagesModule');
        if ($rootcat) {
            // create an entry in the categories registry to the Main property
            if (!CategoryRegistryUtil::insertEntry($this->bundle->getName(), 'PageEntity', 'Main', $rootcat['id'])) {
                throw new \Exception('Cannot insert Category Registry entry.');
            }
        } else {
            throw new NotFoundHttpException('Root category not found.');
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
        $em = $this->container->get('doctrine.entitymanager');
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
        $em->persist($page);
        $category = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/ZikulaPagesModule/Category1');
        $catEntity = $em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', $category['id']);
        $categoryRegistry = CategoryRegistryUtil::getRegisteredModuleCategoriesIds('ZikulaPagesModule', 'PageEntity');
        $categoryAssociation = new CategoryEntity($categoryRegistry['Main'], $catEntity, $page);
        $arrayCollection = new ArrayCollection([$categoryAssociation]);
        $page->setCategories($arrayCollection);
        $em->flush();
    }

    public function setBundle(AbstractBundle $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTranslator($container->get('translator'));
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
