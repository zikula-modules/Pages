<?php

/**
 * Pages.
 *
 * @copyright Zikula Team (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula Team <info@ziku.la>.
 *
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio 1.5.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\PagesModule\Listener\Base;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Event\ActiveUserPostCreatedEvent;
use Zikula\UsersModule\Event\ActiveUserPostDeletedEvent;
use Zikula\UsersModule\Event\ActiveUserPostUpdatedEvent;
use Zikula\PagesModule\Entity\Factory\EntityFactory;

/**
 * Event handler base class for user-related events.
 */
abstract class AbstractUserListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    
    /**
     * @var EntityFactory
     */
    protected $entityFactory;
    
    /**
     * @var CurrentUserApiInterface
     */
    protected $currentUserApi;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    public function __construct(
        TranslatorInterface $translator,
        EntityFactory $entityFactory,
        CurrentUserApiInterface $currentUserApi,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->entityFactory = $entityFactory;
        $this->currentUserApi = $currentUserApi;
        $this->logger = $logger;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            ActiveUserPostCreatedEvent::class => ['create', 5],
            ActiveUserPostUpdatedEvent::class => ['update', 5],
            ActiveUserPostDeletedEvent::class => ['delete', 5],
        ];
    }
    
    /**
     * Listener for the `ActiveUserPostCreatedEvent`.
     *
     * Occurs after a user account is created. All handlers are notified.
     * It does not apply to creation of a pending registration.
     * The full user record created is available as the subject.
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the user record that was created.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function create(ActiveUserPostCreatedEvent $event): void
    {
    }
    
    /**
     * Listener for the `ActiveUserPostUpdatedEvent`.
     *
     * Occurs after a user is updated. All handlers are notified.
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The User property is the *new* data. The oldUser property is the *old* data.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     */
    public function update(ActiveUserPostUpdatedEvent $event): void
    {
    }
    
    /**
     * Listener for the `ActiveUserPostDeletedEvent`.
     *
     * Occurs after the deletion of a user account.
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     *
     *
     * You can access the user and date in the event.
     *
     * The user:
     *     `echo 'UID: ' . $event->getUser()->getUid();`
     *
     * Check if user is really deleted or "ghosted":
     *     `if ($event->isFullDeletion())`
     */
    public function delete(ActiveUserPostDeletedEvent $event): void
    {
        if (!$event->isFullDeletion()) {
            return;
        }
    
        $userId = $event->getUser()->getUid();
        
        $repo = $this->entityFactory->getRepository('page');
        // set creator to admin (UsersConstant::USER_ID_ADMIN) for all pages created by this user
        $repo->updateCreator(
            $userId,
            UsersConstant::USER_ID_ADMIN,
            $this->translator,
            $this->logger,
            $this->currentUserApi
        );
        
        // set last editor to admin (UsersConstant::USER_ID_ADMIN) for all pages updated by this user
        $repo->updateLastEditor(
            $userId,
            UsersConstant::USER_ID_ADMIN,
            $this->translator,
            $this->logger,
            $this->currentUserApi
        );
        
        $logArgs = [
            'app' => 'ZikulaPagesModule',
            'user' => $this->currentUserApi->get('uname'),
            'entities' => 'pages',
        ];
        $this->logger->notice(
            '{app}: User {user} has been deleted, so we deleted/updated corresponding {entities}, too.',
            $logArgs
        );
    }
}
