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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Core\Controller\AbstractController;
use Zikula\PagesModule\AdminAuthInterface;
use Zikula\PagesModule\Form\Type\ConfigType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 */
class ConfigController extends AbstractController implements AdminAuthInterface
{
    /**
     * @Route("/config")
     * @Template("@ZikulaPagesModule/Config/config.html.twig")
     * @Theme("admin")
     *
     * @param Request $request
     * @return RedirectResponse|array
     */
    public function configAction(Request $request)
    {
        $form = $this->createForm(ConfigType::class, $this->getVars(), [
            'translator' => $this->getTranslator()
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulapagesmodule_admin_index'));
        }

        return [
            'form' => $form->createView()
        ];
    }
}
