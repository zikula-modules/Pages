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

namespace Zikula\PagesModule\Listener\Base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\UsersModule\Event\ActiveUserPreCreatedEvent;
use Zikula\UsersModule\Event\RegistrationPostApprovedEvent;
use Zikula\UsersModule\Event\RegistrationPostCreatedEvent;
use Zikula\UsersModule\Event\RegistrationPostDeletedEvent;
use Zikula\UsersModule\Event\RegistrationPostSuccessEvent;
use Zikula\UsersModule\Event\RegistrationPostUpdatedEvent;

/**
 * Event handler base class for user registration events.
 */
abstract class AbstractUserRegistrationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ActiveUserPreCreatedEvent::class     => ['createVeto', 5],
            RegistrationPostSuccessEvent::class  => ['succeeded', 5],
            RegistrationPostCreatedEvent::class  => ['create', 5],
            RegistrationPostUpdatedEvent::class  => ['update', 5],
            RegistrationPostDeletedEvent::class  => ['delete', 5],
            RegistrationPostApprovedEvent::class => ['forceApproval', 5]
        ];
    }
    
    /**
     * Listener for the `ActiveUserPreCreatedEvent`.
     *
     * Occurs when the Registration process is determining whether to create a 'registration' or a 'full user'.
     *
     * If the User hasn't been persisted, then there will be no Uid.
     *
     * A handler that needs to veto a registration should call `stopPropagation()`. This will prevent other handlers
     * from receiving the event, will return to the registration process, and will prevent the registration from
     * creating a 'full user' record.
     *
     * For example an authentication method may veto a registration attempt if it requires a user to verify some
     * registration data by email.
     *
     * It is assumed that the authentication method will have notified the user of required steps to prevent future
     * vetoes. And provide the methods to correct the issue and process the steps.
     *
     * Because this event will not necessarily notify ALL listeners (if propagation is stopped) it CANNOT be relied upon
     * to effect change of any kind with regard to the entity.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function createVeto(ActiveUserPreCreatedEvent $event): void
    {
    }
    
    /**
     * Listener for the `RegistrationPostSuccessEvent`.
     *
     * Occurs after a user has successfully registered a new account in the system. It will follow either a
     * `RegistrationPostCreatedEvent`, or a `ActiveUserPostCreatedEvent`, depending on the result of the registration process,
     * the information provided by the user, and several configuration options set in the Users module. The resultant record
     * might be a fully activated user record, or it might be a registration record pending approval, e-mail
     * verification, or both.
     *
     * If the registration record is a fully activated user, and the Users module is configured for automatic log-in,
     * then the system's next step (without any interaction from the user) will be the log-in process. All the customary
     * events that might fire during the log-in process could be fired at this point, including (but not limited to)
     * `Zikula\UsersModule\Event\UserPreLoginSuccessEvent` (which might result in the user having to perform some action
     * in order to proceed with the log-in process), `user.login.succeeded`, and/or `user.login.failed`.
     *
     * The `redirectUrl` property controls where the user will be directed at the end of the registration process.
     * Initially, it will be blank, indicating that the default action should be taken. The default action depends on two
     * things: first, whether the result of the registration process is a registration request record or is a full user record,
     * and second, if the record is a full user record then whether automatic log-in is enabled or not.
     *
     * If a `redirectUrl` is specified by any entity intercepting and processing this event, then
     * how that redirect URL is handled depends on whether the registration process produced a registration request or a full user
     * account record, and if a full user account record was produced then it depends on whether automatic log-in is enabled or
     * not.
     *
     * If the result of the registration process is a registration request record, then by specifying a redirect URL on the event
     * the default action will be overridden, and the user will be redirected to the specified URL at the end of the process.
     *
     * If the result of the registration process is a full user account record and automatic log-in is disabled, then by specifying
     * a redirect URL on the event the default action will be overridden, and the user will be redirected to the specified URL at
     * the end of the process.
     *
     * If the result of the registration process is a full user account record and automatic log-in is enabled, then the user is
     * directed automatically into the log-in process. A redirect URL specified on the event will be passed to the log-in process
     * as the default redirect URL to be used at the end of the log-in process. Note that the user has NOT been automatically
     * redirected to the URL specified on the event. Also note that the log-in process issues its own events, and any one of them
     * could direct the user away from the log-in process and ultimately from the URL specified in this event. Note especially that
     * the log-in process issues its own `module.users.ui.login.succeeded` event that includes the opportunity to set a redirect URL.
     * The URL specified on this event, as mentioned previously, is passed to the log-in process as the default redirect URL, and
     * therefore is offered on the `module.users.ui.login.succeeded` event as the default. Any handler of that event, however, has
     * the opportunity to change the redirect URL offered. A RegistrationPostSuccessEvent::class handler can reliably predict
     * whether the user will be directed into the log-in process automatically by inspecting the Users module variable
     * `Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN` (which evaluates to `'reg_autologin'`), and by inspecting the `'activated'`
     * status of the registration or user object received.
     *
     * An event handler should carefully consider whether changing the `'redirectUrl'` argument is appropriate. First, the user may
     * be expecting to return to the log-in screen . Being redirected to a different page might be disorienting to the user. Second,
     * an event handler that was notified prior to the current handler may already have changed the `'redirectUrl'`.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function succeeded(RegistrationPostSuccessEvent $event): void
    {
    }
    
    /**
     * Listener for the `RegistrationPostCreatedEvent`.
     *
     * Occurs after a registration record is created, either through the normal user registration process,
     * or through the administration panel for the Users module. This event will not fire if the result of the
     * registration process is a full user record. Instead, an `ActiveUserPostCreatedEvent` will fire.
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the UserEntity that was created.
     * This event occurs before the `$authenticationMethod->register()` method is called.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function create(RegistrationPostCreatedEvent $event): void
    {
    }
    
    /**
     * Listener for the `RegistrationPostUpdatedEvent`.
     *
     * Occurs after a registration record is updated (likely through the admin panel, but not guaranteed).
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the UserEntity, with the updated values. The event data contains the
     * original UserEntity in an array `['oldValue' => $originalUser]`.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function update(RegistrationPostUpdatedEvent $event): void
    {
    }
    
    /**
     * Listener for the `RegistrationPostDeletedEvent`.
     *
     * Occurs after a registration record is deleted. This could occur as a result of the administrator deleting
     * the record through the approval/denial process, or it could happen because the registration request expired.
     * This event will not fire if a registration record is converted to a full user account record. Instead,
     * an `ActiveUserPostCreatedEvent` will fire. This is a storage-level event, not a UI event. It should not be
     * used for UI-level actions such as redirects. The subject of the event is set to the Uid being deleted.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function delete(RegistrationPostDeletedEvent $event): void
    {
    }
    
    /**
     * Listener for the `RegistrationPostApprovedEvent`.
     *
     * Occurs when an administrator approves a registration.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     */
    public function forceApproval(RegistrationPostApprovedEvent $event): void
    {
    }
}