<?php
namespace Tymy\Module\PushNotification\Manager;

use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\PushNotification\Model\PushNotification;

/**
 * Description of NotificationGenerator
 *
 * @author kminekmatej, 15. 11. 2021, 13:10:33
 */
class NotificationGenerator
{

    public function newPost(Discussion $discussion, Post $post)
    {
        return new PushNotification("{$post->getCreatedBy()->getCallName()} posted in {$discussion->getWebName()}", $post->getPost(), null, null);
    }

}
