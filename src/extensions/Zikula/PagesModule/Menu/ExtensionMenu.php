<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Menu;

use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\PagesModule\Helper\FeatureActivationHelper;
use Zikula\PagesModule\Menu\Base\AbstractExtensionMenu;

/**
 * This is the extension menu service implementation class.
 */
class ExtensionMenu extends AbstractExtensionMenu
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FeatureActivationHelper
     */
    private $featureActivationHelper;

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        $menu = parent::get($type);

        if (self::TYPE_USER !== $type) {
            return null === $menu || 0 === $menu->count() ? null : $menu;
        }

        $pagesLabel = $this->translator->trans('Pages', [], 'page');
        $pagesLink = $menu->getChild($pagesLabel);
        if (null !== $pagesLink) {
            $pagesLink
                ->setLabel('Pages list')
                ->setAttribute('icon', 'fas fa-list')
            ;
        }

        if ($this->featureActivationHelper->hasCategories('page')) {
            $menu->addChild('Categories', [
                'route' => 'zikulapagesmodule_page_view',
                'routeParameters' => ['list' => 'categories']
            ])
                ->setAttribute('icon', 'fas fa-tags')
            ;
        }

        return 0 === $menu->count() ? null : $menu;
    }

    /**
     * @required
     */
    public function setAdditionalDependencies(
        TranslatorInterface $translator,
        FeatureActivationHelper $featureActivationHelper
    ): void {
        $this->translator = $translator;
        $this->featureActivationHelper = $featureActivationHelper;
    }
}
