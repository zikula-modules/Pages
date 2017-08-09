<?php

/*
 * This file is part of the ZikulaPagesModule package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PagesModule;

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\CategoriesModule\Entity\CategoryAttributeEntity;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\PagesModule\Entity\CategoryAssignmentEntity;
use Zikula\PagesModule\Entity\PageEntity;

/**
 * Provides module installation and upgrade services.
 */
class PagesModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        PageEntity::class,
        CategoryAssignmentEntity::class
    ];

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
            $this->schemaTool->create($this->entities);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }
        // insert default category
        try {
            $this->createCategoryTree();
        } catch (\Exception $e) {
            $this->addFlash('error', $this->__f('Did not create default categories (%s).', ['%s' => $e->getMessage()]));
        }
        // set up config variables
        $modvars = [
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
        ];
        $this->setVars($modvars);
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
     * @param string $oldversion Version number string to upgrade from
     *
     * @return mixed True on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Only support upgrade from version 2.5.1 and up. Notify users if they have a version below that one.
        if (version_compare($oldversion, '2.5.1', '<=')) {
            $this->addFlash('error', $this->__('Notice: This version does not support upgrades from versions of Pages less than 2.5.1. Please upgrade to 2.5.1 before attempting this upgrade.'));

            return false;
        }
        $connection = $this->entityManager->getConnection();

        switch ($oldversion) {
            case '2.5.1':
                // create categories table
                try {
                    $this->schemaTool->create(['Zikula\PagesModule\Entity\CategoryAssignmentEntity']);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());

                    return false;
                }
                // move relations from categories_mapobj to pages_category
                // then delete old data
                $sqls = [];
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
                        $this->addFlash('error', $e->getMessage());
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
            case '3.1.0':
            case '3.2.0':
                if (method_exists($this->hookApi, 'uninstallSubscriberHooks')) {
                    // method only exists in core < 2.0.0
                    $this->hookApi->uninstallSubscriberHooks($this->bundle->getMetaData());
                }
            case '3.2.1': // current version
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
        $this->schemaTool->drop($this->entities);
        // Delete any module variables
        $this->delVars();
        // Delete entries from category registry
        $registries = $this->container->get('zikula_categories_module.category_registry_repository')->findBy(['modname' => $this->bundle->getName()]);
        foreach ($registries as $registry) {
            $this->entityManager->remove($registry);
        }
        $this->entityManager->flush();
        // Deletion successful
        return true;
    }

    /**
     * create the category tree
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If Root category not found
     * @throws \Exception
     *
     * @return boolean
     */
    private function createCategoryTree()
    {
        $locale = $this->container->get('request_stack')->getCurrentRequest()->getLocale();
        $repo = $this->container->get('zikula_categories_module.category_repository');
        // create pages root category
        $parent = $repo->findOneBy(['name' => 'Modules']);
        $pagesRoot = new CategoryEntity();
        $pagesRoot->setParent($parent);
        $pagesRoot->setName($this->bundle->getName());
        $pagesRoot->setDisplay_name([
            $locale => $this->__('Pages', 'zikulapagesmodule', $locale)
        ]);
        $pagesRoot->setDisplay_desc([
            $locale => $this->__('Static Pages', 'zikulapagesmodule', $locale)
        ]);
        $this->entityManager->persist($pagesRoot);
        // create children
        $category1 = new CategoryEntity();
        $category1->setParent($pagesRoot);
        $category1->setName('Category1');
        $category1->setDisplay_name([
            $locale => $this->__('Category 1', 'zikulapagesmodule', $locale)
        ]);
        $category1->setDisplay_desc([
            $locale => $this->__('Initial sub-category created on install', 'zikulapagesmodule', $locale)
        ]);
        $attribute = new CategoryAttributeEntity();
        $attribute->setAttribute('color', '#99ccff');
        $category1->addAttribute($attribute);
        $this->entityManager->persist($category1);

        $category2 = new CategoryEntity();
        $category2->setParent($pagesRoot);
        $category2->setName('Category2');
        $category2->setDisplay_name([
            $locale => $this->__('Category 2', 'zikulapagesmodule', $locale)
        ]);
        $category2->setDisplay_desc([
            $locale => $this->__('Initial sub-category created on install', 'zikulapagesmodule', $locale)
        ]);
        $attribute = new CategoryAttributeEntity();
        $attribute->setAttribute('color', '#cceecc');
        $category2->addAttribute($attribute);
        $this->entityManager->persist($category2);

        // create Registry
        $registry = new CategoryRegistryEntity();
        $registry->setCategory($pagesRoot);
        $registry->setEntityname('PageEntity');
        $registry->setModname($this->bundle->getName());
        $registry->setProperty('Main');
        $this->entityManager->persist($registry);

        $this->entityManager->flush();

        return true;
    }

    /**
     * create a sample page
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function createIntroPage()
    {
        $locale = $this->container->get('request_stack')->getCurrentRequest()->getLocale();
        $repo = $this->container->get('zikula_categories_module.category_repository');

        $content = 'This is a demonstration page. You can use Pages to create simple static content pages. It is excellent '
            . 'if you only need basic html for your pages. You can also utilize the Scribite module for WYSIWYG '
            . 'content creation. It is well suited for informational articles, documents and other "long term" type '
            . 'content items.' . '<br /><br />'
            . 'Pages is a hookable module which allows you to hook EZComments or other hook providers to extend the '
            . 'capabilities of your module.';
        $data = [
            'title' => $this->__('Welcome to Pages content manager'),
            'urltitle' => $this->__('welcome-to-pages-content-manager'),
            'content' => $content,
            'language' => $locale];
        $page = new PageEntity();
        $page->setDefaultsFromModVars($this->getVars());
        $page->merge($data);
        $currentUserId = $this->container->get('zikula_users_module.current_user')->get('uid');
        $currentUser = $this->container->get('zikula_users_module.user_repository')->find($currentUserId);
        $page->setCreator($currentUser);
        $page->setUpdater($currentUser);
        $this->entityManager->persist($page);
        $category = $repo->findOneBy(['name' => 'Category1']);
        $categoryRegistry = $this->container->get('zikula_categories_module.category_registry_repository')->findOneBy([
            'modname' => $this->bundle->getName(),
            'entityname' => 'PageEntity',
            'property' => 'Main'
        ]);
        $categoryAssociation = new CategoryAssignmentEntity($categoryRegistry->getId(), $category, $page);
        $arrayCollection = new ArrayCollection([$categoryAssociation]);
        $page->setCategoryAssignments($arrayCollection);
        $this->entityManager->flush();
    }
}
