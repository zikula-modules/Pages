<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.5.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Listener\Base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostEnabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostInstallEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent;

/**
 * Event handler base class for extension installer events.
 */
abstract class AbstractInstallerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ExtensionPostInstallEvent::class      => ['extensionInstalled', 5],
            ExtensionPostCacheRebuildEvent::class => ['extensionPostInstalled', 5],
            ExtensionPostUpgradeEvent::class      => ['extensionUpgraded', 5],
            ExtensionPostEnabledEvent::class      => ['extensionEnabled', 5],
            ExtensionPostDisabledEvent::class     => ['extensionDisabled', 5],
            ExtensionPostRemoveEvent::class       => ['extensionRemoved', 5]
        ];
    }
    
    /**
     * Listener for the `ExtensionPostInstallEvent`.
     *
     * Occurs when an extension has been successfully installed but before the Cache has been reloaded.
     */
    public function extensionInstalled(ExtensionPostInstallEvent $event): void
    {
    }
    
    /**
     * Listener for the `ExtensionPostCacheRebuildEvent`.
     *
     * Occurs when an extension has been successfully installed
     * and then the Cache has been reloaded after a second Request.
     */
    public function extensionPostInstalled(ExtensionPostCacheRebuildEvent $event): void
    {
    }
    
    /**
     * Listener for the `ExtensionPostUpgradeEvent`.
     *
     * Occurs when an extension has been upgraded to a newer version.
     */
    public function extensionUpgraded(ExtensionPostUpgradeEvent $event): void
    {
    }
    
    /**
     * Listener for the `ExtensionPostEnabledEvent`.
     *
     * Occurs when an extension has been enabled after it was previously disabled.
     */
    public function extensionEnabled(ExtensionPostEnabledEvent $event): void
    {
    }
    
    /**
     * Listener for the `ExtensionPostDisabledEvent`.
     *
     * Occurs when an extension has been disabled.
     */
    public function extensionDisabled(ExtensionPostDisabledEvent $event): void
    {
    }
    
    /**
     * Listener for the `ExtensionPostRemoveEvent`.
     *
     * Occurs when an extension has been removed entirely.
     */
    public function extensionRemoved(ExtensionPostRemoveEvent $event): void
    {
    }
}
