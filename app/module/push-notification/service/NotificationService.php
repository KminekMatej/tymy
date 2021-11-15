<?php
namespace Tymy\Module\PushNotification\Service;

use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\PushNotification\Model\PushNotification;

/**
 * Description of NotificationGenerator
 *
 * @author kminekmatej, 15. 11. 2021, 13:10:33
 */
class NotificationService
{

    private bool $isQueue = false;
    private WebPush $webPush;
    private UserManager $userManager;

    public function __construct(WebPush $webPush, UserManager $userManager)
    {
        $this->webPush = $webPush;
        $this->userManager = $userManager;
    }

    public function newPost(Discussion $discussion, Post $post)
    {
        return new PushNotification("{$post->getCreatedBy()->getCallName()} posted in {$discussion->getWebName()}", $post->getPost(), null, null);
    }

    /**
     * Send Push notification message to subscribed user
     *
     * @param PushNotification $notification Push notification object to be sent. Can be generated using NotificationGenerator
     * @param int $userId ID of user to send Push notification
     * @param bool $flush Instant flush message
     */
    public function notifyUser(PushNotification $notification, int $userId)
    {
        try {
            foreach ($this->getList() as $subscriber) {
                /* @var $subscriber Subscriber */
                if ($userId !== $subscriber->userId) {
                    continue;
                }
                $this->isQueue = true;
                $report = $this->webPush->sendOneNotification(
                    Subscription::create(json_decode($subscriber->subscription, true)), // subscription
                    json_encode($notification->jsonSerialize()) // payload
                );
                $this->processReport($subscriber, $report);
            }
        } catch (ErrorException $e) {
            Debugger::log('WebPush ErrorException: ' . $e->getMessage(), ILogger::EXCEPTION);
        }
    }

    /**
     * Notify multiple users by their ids
     * 
     * @param PushNotification $notification
     * @param int[] $userIds
     * @return void
     */
    public function notifyUsers(PushNotification $notification, array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->notifyUser($notification, $userId);
        }
    }

    /**
     * Notify every subscriber with PushNotification object.
     * 
     * @param PushNotification $notification
     * @param int[] $userIds
     * @return void
     */
    public function notifyEveryone(PushNotification $notification): void
    {
        try {
            foreach ($this->getList() as $subscriber) {
                /* @var $subscriber Subscriber */
                $this->isQueue = true;
                $report = $this->webPush->sendOneNotification(
                    Subscription::create(json_decode($subscriber->getSubscription(), true)), // subscription
                    json_encode($notification->jsonSerialize()) // payload
                );
                $this->processReport($subscriber, $report);
            }
        } catch (ErrorException $e) {
            Debugger::log('WebPush ErrorException: ' . $e->getMessage(), ILogger::EXCEPTION);
        }
    }

    /**
     * Deletes subscriber from database if its already expired.
     * May contain another post-processing tasks
     * 
     * @param Subscriber $subscriber
     * @param MessageSentReport $report
     * @return void
     */
    private function processReport(Subscriber $subscriber, MessageSentReport $report): void
    {
        if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
            $this->delete($subscriber->getId());    //sending to void subscription - delete it from DB to avoid ghosts
        }
    }

    /**
     * Return bool value based on if there is
     * some push notification in queue
     * @return bool
     */
    public function isQueue()
    {
        return $this->isQueue;
    }
}
