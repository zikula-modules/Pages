<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version)
 * @package Pages
 * @see https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing
 */

namespace Zikula\PagesModule\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\HookBundle\Hook\Hook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationResponse;
use Zikula\Core\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Zikula\Core\RouteUrl;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Form\Type\PageType;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\PagesModule\AdminAuthInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 *
 * Class AdminFormController
 * @package Zikula\PagesModule\Controller
 */
class AdminFormController extends AbstractController implements AdminAuthInterface
{
    /**
     * @Route("/edit/{page}")
     * @Theme("admin")
     * @param Request $request
     * @param PageEntity $page
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, PageEntity $page = null)
    {
        if (null === $page) {
            $page = new PageEntity(); // sets defaults in constructor
        }

        $form = $this->createForm(new PageType(), $page);

        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            if ($this->hookValidates($form, 'validate_edit')) {
                $em->persist($page);
                $em->flush();
                $this->addFlash('status', __('Page saved!'));

                $this->notifyHooks(
                    new ProcessHook(
                        $page->getPageid(),
                        new RouteUrl('zikulapagesmodule_user_display', ['urltitle' => $page->getUrltitle()])
                    ),
                    "pages.ui_hooks.pages.process_edit"
                );

                return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
            }
        }

        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render('ZikulaPagesModule:Admin:modify.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/config")
     * @Theme("admin")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function configAction(Request $request)
    {
        $form = $this->createFormBuilder($this->getVars())
            ->add('enablecategorization', 'checkbox', array('label' => __('Enable categorization'),
                'required' => false))
            ->add('itemsperpage', 'number', array('label' => __('Items per page'),
                'constraints' => array(
                    new Assert\GreaterThan(array('value' => 0)),
                )))
            ->add('def_displaywrapper', 'checkbox', array('label' => __('Display additional information'), 'required' => false))
            ->add('def_displaytitle', 'checkbox', array('label' => __('Display page title'), 'required' => false))
            ->add('def_displaycreated', 'checkbox', array('label' => __('Display page creation date'), 'required' => false))
            ->add('def_displayupdated', 'checkbox', array('label' => __('Display page update date'), 'required' => false))
            ->add('def_displaytextinfo', 'checkbox', array('label' => __('Display page text statistics'), 'required' => false))
            ->add('def_displayprint', 'checkbox', array('label' => __('Display page print link'), 'required' => false))
            ->add('addcategorytitletopermalink', 'checkbox', array('label' => __('Add category title to permalink'), 'required' => false, 'disabled' => true))
            ->add('showpermalinkinput', 'checkbox', array('label' => __('Show permalink input field'), 'required' => false, 'disabled' => true))
            ->add('save', 'submit', array('label' => 'Save'))
            ->add('cancel', 'submit', array('label' => 'Cancel'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', __('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', __('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
        }

        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render('ZikulaPagesModule:Admin:config.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{page}")
     * @Theme("admin")
     * @param Request $request
     * @param PageEntity $page
     * @return RedirectResponse|Response
     */
    public function deleteAction(Request $request, PageEntity $page)
    {
        $form = $this->createFormBuilder()
            ->add('Delete', 'submit', array('label' => 'Delete'))
            ->add('cancel', 'submit', array('label' => 'Cancel'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('Delete')->isClicked()) {
                if ($this->hookValidates($form, 'validate_delete')) {
                    // Save page id for use in hook event. It is set to null during the entitymanager flush.
                    $pageId = $page->getPageid();

                    /** @var \Doctrine\ORM\EntityManager $em */
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($page);
                    $em->flush();
                    $this->addFlash('status', __('Done! Page deleted.'));

                    $this->notifyHooks(
                        new ProcessHook(
                            $pageId,
                            new RouteUrl('zikulapagesmodule_user_display', ['urltitle' => $page->getUrltitle()])
                        ),
                        "pages.ui_hooks.pages.process_delete"
                    );

                    return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', __('Operation cancelled.'));

                return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
            }
        }

        $request->attributes->set('_legacy', true); // forces template to render inside old theme

        return $this->render('ZikulaPagesModule:Admin:delete.html.twig', array(
            'page' => $page,
            'form' => $form->createView(),
        ));
    }

    /**
     * Checks whether or not the hook validates.
     *
     * @param Form $form
     * @param string $event
     *
     * @return bool
     */
    private function hookValidates(Form $form, $event)
    {
        $validationHook = new ValidationHook();
        /** @var ValidationHook $validationHook */
        $validationHook = $this->notifyHooks($validationHook, "pages.ui_hooks.pages.$event");
        $hookvalidators = $validationHook->getValidators();

        if (!$hookvalidators->hasErrors()) {
            return true;
        }

        /** @var ValidationResponse $validationResponse */
        foreach ($hookvalidators as $validationResponse) {
            foreach ($validationResponse->getErrors() as $error) {
                $form->addError(new FormError($error));
            }
        }

        return false;
    }

    /**
     * Notifies subscribers of the given hook.
     *
     * @param Hook $hook
     * @param $name
     *
     * @return Hook
     */
    private function notifyHooks(Hook $hook, $name)
    {
        return $this->get('hook_dispatcher')->dispatch($name, $hook);
    }
}
