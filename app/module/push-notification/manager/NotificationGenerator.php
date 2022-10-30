<?php

namespace Tymy\Module\PushNotification\Manager;

use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User as User2;

/**
 * Description of NotificationGenerator
 *
 * @author kminekmatej, 15. 11. 2021, 13:10:33
 */
class NotificationGenerator
{
    public const CREATE_POST = "create-post";
    public const CREATE_EVENT = "create-event";
    public const DELETE_EVENT = "delete-event";
    public const UPDATE_EVENT_TIME = "update-event-time";

    public function __construct(private User $user, private TeamManager $teamManager, private UserManager $userManager)
    {
    }

    public function createPost(Discussion $discussion, Post $post): PushNotification
    {
        return new PushNotification(
            self::CREATE_POST,
            $this->user->getId(),
            $this->teamManager->getTeam()->getId(),
            "{$post->getCreatedBy()->getCallName()} posted in {$discussion->getCaption()}",
            $post->getPost(),
            null,
            null,
            [
                "discussionId" => $discussion->getId()
            ]
        );
    }

    public function createEvent(Event $event): PushNotification
    {
        $user = $this->userManager->getById($this->user->getId());

        return new PushNotification(
            self::CREATE_EVENT,
            $this->user->getId(),
            $this->teamManager->getTeam()->getId(),
            "{$user->getCallName()} created new event",
            "{$event->getCaption()} on " . $event->getStartTime()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT),
            null,
            null,
            [
            ]
        );
    }

    public function deleteEvent(Event $event): PushNotification
    {
        $user = $this->userManager->getById($this->user->getId());

        return new PushNotification(
            self::DELETE_EVENT,
            $this->user->getId(),
            $this->teamManager->getTeam()->getId(),
            "{$user->getCallName()} delete event",
            "{$event->getCaption()} on " . $event->getStartTime()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT),
            null,
            null,
            [
            ]
        );
    }

    public function changeEventTime(Event $event, DateTime $previousStartTime): PushNotification
    {
        $user = $this->userManager->getById($this->user->getId());

        return new PushNotification(
            self::UPDATE_EVENT_TIME,
            $this->user->getId(),
            $this->teamManager->getTeam()->getId(),
            "{$user->getCallName()} changed event time",
            "{$event->getCaption()} starts on " . $event->getStartTime()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT) . " (previously " . $previousStartTime->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT) . ")",
            null,
            null,
            [
            ]
        );
    }
}
