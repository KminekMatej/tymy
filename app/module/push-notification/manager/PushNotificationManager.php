<?php

namespace Tymy\Module\PushNotification\Manager;

use ErrorException;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Nette\NotImplementedException;
use Tracy\Debugger;
use Tracy\ILogger;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\PushNotification\Mapper\SubscriberMapper;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;

/**
 * PushNotificationManager is a class for handling read operations upon Push Notification subscription table.
 */
class PushNotificationManager extends BaseManager
{
    public function __construct(ManagerFactory $managerFactory, private WebPush $webPush, private ApplePush $applePush, private FirebasePush $firebasePush)
    {
        parent::__construct($managerFactory);
    }

    /**
     * Get Push Notification subscription based on user ID and subscription
     */
    public function getByUserAndSubscription(int $userId, string $subscription): ?\Tymy\Module\Core\Model\BaseModel
    {
        return $this->map($this->database->table(Subscriber::TABLE)
                    ->where("user_id", $userId)
                    ->where("subscription", $subscription)->fetch());
    }

    /**
     * Get Subscribers by userIds
     *
     * @param int[] User ids
     * @return Subscriber[]
     */
    private function getByUsers(array $userIds): array
    {
        return $this->mapAll($this->database->table(Subscriber::TABLE)
                    ->where("user_id", $userIds)
                    ->where("user_id != ?", $this->user->getId())   //do not notify myself
                    ->fetchAll());
    }

    /**
     * Flush (send) all push notification in queue
     */
    public function flush(): void
    {
        $this->webPush->flush();
    }

    protected function getClassName(): string
    {
        return Subscriber::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    protected function getScheme(): array
    {
        return SubscriberMapper::scheme();
    }

    public function canEdit(BaseModel $entity, int $userId): bool
    {
        return true;
    }

    public function canRead(BaseModel $entity, int $userId): bool
    {
        return true;
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        return $this->map($this->createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        return $this->deleteRecord($resourceId);
    }

    /**
     * @return mixed[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        //no-one is allowed
        return [];
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException();
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Send Push notification message to subscribed user
     *
     * @param PushNotification $notification Push notification object to be sent. Can be generated using NotificationGenerator
     * @param int $userId ID of user to send Push notification
     * @param bool $flush Instant flush message
     */
    public function notifyUser(PushNotification $notification, int $userId): void
    {
        $this->notifyUsers($notification, [$userId]);
    }

    /**
     * Notify all subscribers registered through Web push messaging
     * @param Subscriber[] $subscribers
     */
    private function webPushBulk(PushNotification $notification, array $subscribers): void
    {
        try {
            foreach ($subscribers as $subscriber) {
                assert($subscriber instanceof Subscriber);
                $report = $this->webPush->sendOneNotification(
                    Subscription::create(\json_decode($subscriber->getSubscription(), true, 512, JSON_THROW_ON_ERROR)), // subscription
                    \json_encode($notification->jsonSerialize(), JSON_THROW_ON_ERROR) // payload
                );
                $this->processReport($subscriber, $report);
            }
        } catch (ErrorException $e) {
            Debugger::log('WebPush ErrorException: ' . $e->getMessage(), ILogger::EXCEPTION);
        }
    }

    /**
     *
     * @param Subscriber[] $subscribers
     */
    private function applePushBulk(PushNotification $notification, array $subscribers): void
    {
        $this->applePush->sendBulkNotifications($subscribers, $notification);

        //delete expired subscribers after sent
        foreach ($this->applePush->getExpiredSubscribers() as $subscriber) {
            assert($subscriber instanceof Subscriber);
            $this->delete($subscriber->getId());
        }
    }

    /**
     *
     * @param Subscriber[] $subscribers
     * @todo
     */
    private function firebasePushBulk(PushNotification $notification, array $subscribers): void
    {
        try {
            $this->firebasePush->sendBulkNotifications($subscribers, $notification);
            //TODO: handle detecting expired subsriptions here
        } catch (ErrorException $e) {
            Debugger::log('FCM Push ErrorException: ' . $e->getMessage(), ILogger::EXCEPTION);
        }
    }

    /**
     * Notify multiple users by their ids
     *
     * @param int[] $userIds
     */
    public function notifyUsers(PushNotification $notification, array $userIds): void
    {
        $appleSubscriptions = [];
        $firebaseSubscriptions = [];
        $webSubscriptions = [];

        foreach ($this->getByUsers($userIds) as $subscriber) {
            assert($subscriber instanceof Subscriber);
            switch ($subscriber->getType()) {
                case Subscriber::TYPE_WEB:
                    $webSubscriptions[] = $subscriber;
                    break;
                case Subscriber::TYPE_APNS:
                    $appleSubscriptions[] = $subscriber;
                    break;
                case Subscriber::TYPE_FCM:
                    $firebaseSubscriptions[] = $subscriber;
                    break;
            }
        }

        if (!empty($webSubscriptions)) {
            $this->webPushBulk($notification, $webSubscriptions);
        }

        if (!empty($appleSubscriptions)) {
            $this->applePushBulk($notification, $appleSubscriptions);
        }

        if (!empty($firebaseSubscriptions)) {
            $this->firebasePushBulk($notification, $firebaseSubscriptions);
        }
    }

    /**
     * Notify every subscriber with PushNotification object.
     *
     * @param int[] $userIds
     */
    public function notifyEveryone(PushNotification $notification): void
    {
        $appleSubscriptions = [];
        $androidSubscriptions = [];
        $webSubscriptions = [];

        foreach ($this->getList() as $subscriber) {
            if ($subscriber->getUserId() == $this->user->getId()) {
                continue; //do not notify myself
            }

            assert($subscriber instanceof Subscriber);
            switch ($subscriber->getType()) {
                case Subscriber::TYPE_WEB:
                    $webSubscriptions[] = $subscriber;
                    break;
                case Subscriber::TYPE_APNS:
                    $appleSubscriptions[] = $subscriber;
                    break;
                case Subscriber::TYPE_FCM:
                    $androidSubscriptions[] = $subscriber;
                    break;
            }
        }

        if (!empty($webSubscriptions)) {
            $this->webPushBulk($notification, $webSubscriptions);
        }

        if (!empty($appleSubscriptions)) {
            $this->applePushBulk($notification, $appleSubscriptions);
        }

        if (!empty($androidSubscriptions)) {
            $this->firebasePushBulk($notification, $androidSubscriptions);
        }
    }

    /**
     * Deletes subscriber from database if its already expired.
     * May contain another post-processing tasks
     */
    private function processReport(Subscriber $subscriber, MessageSentReport $report): void
    {
        if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
            $this->delete($subscriber->getId());    //sending to void subscription - delete it from DB to avoid ghosts
        }
    }
}
