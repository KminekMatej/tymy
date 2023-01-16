<?php

namespace Tymy\Module\PushNotification\Manager;

use Nette\Application\LinkGenerator;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;

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

    public function __construct(private User $user, private TeamManager $teamManager, private UserManager $userManager, private LinkGenerator $linkGenerator)
    {
    }

    public function createPost(Discussion $discussion, Post $post): PushNotification
    {
        return (new PushNotification(self::CREATE_POST))
                ->setUserId($this->user->getId())
                ->setTeamId($this->teamManager->getTeam()->getId())
                ->setTitle("{$post->getCreatedBy()->getCallName()} posted in {$discussion->getCaption()}")
                ->setMessage(substr($post->getPost(), 0, 512) . (strlen($post->getPost()) > 512 ? " ..." : ""))
                ->setUrl($this->linkGenerator->link("Discussion:Discussion:", [$discussion->getWebName()]))
                ->addParam("discussionId", $discussion->getId());
    }

    public function createEvent(Event $event): PushNotification
    {
        $user = $this->userManager->getById($this->user->getId());

        return (new PushNotification(self::CREATE_EVENT))
                ->setUserId($this->user->getId())
                ->setTeamId($this->teamManager->getTeam()->getId())
                ->setTitle("{$user->getCallName()} created new event")
                ->setMessage("{$event->getCaption()} on " . $event->getStartTime()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT))
                ->setUrl($this->linkGenerator->link("Event:Detail:", [$event->getWebName()]));
    }

    public function deleteEvent(Event $event): PushNotification
    {
        $user = $this->userManager->getById($this->user->getId());

        return (new PushNotification(self::DELETE_EVENT))
                ->setUserId($this->user->getId())
                ->setTeamId($this->teamManager->getTeam()->getId())
                ->setTitle("{$user->getCallName()} deleted event")
                ->setMessage("{$event->getCaption()} on " . $event->getStartTime()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT))
                ->setUrl($this->linkGenerator->link("Event:Default:"));
    }

    public function changeEventTime(Event $event, DateTime $previousStartTime): PushNotification
    {
        $user = $this->userManager->getById($this->user->getId());

        return (new PushNotification(self::UPDATE_EVENT_TIME))
                ->setUserId($this->user->getId())
                ->setTeamId($this->teamManager->getTeam()->getId())
                ->setTitle("{$user->getCallName()} changed event time")
                ->setMessage("{$event->getCaption()} starts on " . $event->getStartTime()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT) . " (previously " . $previousStartTime->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT) . ")")
                ->setUrl($this->linkGenerator->link("Event:Detail:", [$event->getWebName()]));
    }
}
