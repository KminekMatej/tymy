<?php
namespace Tymy\Module\PushNotification\Manager;

use Nette\Security\User;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of NotificationGenerator
 *
 * @author kminekmatej, 15. 11. 2021, 13:10:33
 */
class NotificationGenerator
{

    public const CREATE_POST = "create-post";

    private User $user;
    private TeamManager $teamManager;

    public function __construct(User $user, TeamManager $teamManager)
    {
        $this->user = $user;
        $this->teamManager = $teamManager;
    }

    public function createPost(Discussion $discussion, Post $post)
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
}
