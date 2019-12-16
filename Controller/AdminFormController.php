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

namespace Zikula\PagesModule\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\RouteUrl;
use Zikula\PagesModule\AdminAuthInterface;
use Zikula\PagesModule\Entity\PageEntity;
use Zikula\PagesModule\Form\Type\PageType;
use Zikula\PagesModule\HookSubscriber\FormAwareHookSubscriber;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 *
 * Class AdminFormController
 */
class AdminFormController extends AbstractController implements AdminAuthInterface
{
    /**
     * @Route("/edit/{page}")
     * @Theme("admin")
     *
     * @param Request $request
     * @param PageEntity $page
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, PageEntity $page = null)
    {
        if (null === $page) {
            $page = new PageEntity();
            $page->setDefaultsFromModVars($this->getVars());
        }

        $form = $this->createForm(PageType::class, $page, [
            'translator' => $this->getTranslator(),
            'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(null, $request->getLocale())
        ]);
        $formHook = new FormAwareHook($form);
        $this->get('hook_dispatcher')->dispatch(FormAwareHookSubscriber::SUBSCRIBER_FORMAWARE_TYPE_EDIT, $formHook);

        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            if ($this->hookValidates($form, 'validate_edit')) {
                $em->persist($page);
                $em->flush();
                $this->addFlash('status', $this->__('Page saved!'));
                $routeUrl = new RouteUrl('zikulapagesmodule_user_display', ['urltitle' => $page->getUrltitle()]);
                $this->get('hook_dispatcher')->dispatch(FormAwareHookSubscriber::SUBSCRIBER_FORMAWARE_TYPE_PROCESS_EDIT, new FormAwareResponse($form, $page, $routeUrl));
                $this->get('hook_dispatcher')->dispatch('pages.ui_hooks.pages.process_edit', new ProcessHook($page->getPageid(), $routeUrl));

                return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
            }
        }

        return $this->render('@ZikulaPagesModule/Admin/modify.html.twig', [
            'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ]);
    }

    /**
     * @Route("/delete/{page}")
     * @Theme("admin")
     *
     * @param Request $request
     * @param PageEntity $page
     * @return RedirectResponse|Response
     */
    public function deleteAction(Request $request, PageEntity $page)
    {
        $form = $this->createForm(DeletionType::class);
        $formHook = new FormAwareHook($form);
        $this->get('hook_dispatcher')->dispatch(FormAwareHookSubscriber::SUBSCRIBER_FORMAWARE_TYPE_DELETE, $formHook);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                if ($this->hookValidates($form, 'validate_delete')) {
                    // Save page id for use in hook event. It is set to null during the entitymanager flush.
                    $pageId = $page->getPageid();

                    $em = $this->getDoctrine()->getManager();
                    $em->remove($page);
                    $em->flush();
                    $this->addFlash('status', $this->__('Done! Page deleted.'));

                    $this->get('hook_dispatcher')->dispatch(FormAwareHookSubscriber::SUBSCRIBER_FORMAWARE_TYPE_PROCESS_DELETE, new FormAwareResponse($form, $pageId));
                    $this->get('hook_dispatcher')->dispatch('pages.ui_hooks.pages.process_delete', new ProcessHook($pageId));

                    return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
            }
        }

        return $this->render('@ZikulaPagesModule/Admin/delete.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ]);
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
        $this->get('hook_dispatcher')->dispatch("pages.ui_hooks.pages.${event}", $validationHook);
        $hookValidators = $validationHook->getValidators();

        if (!$hookValidators->hasErrors()) {
            return true;
        }

        foreach ($hookValidators->getErrors() as $error) {
            $form->addError(new FormError($error));
        }

        return false;
    }
}
