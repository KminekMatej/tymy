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

    private bool $isQueue = false;
    private WebPush $webPush;

    public function __construct(ManagerFactory $managerFactory, WebPush $webPush)
    {
        parent::__construct($managerFactory);
        $this->webPush = $webPush;
    }

    /**
     * Get Push Notification subscription based on user ID and subscription
     * @param int $userId
     * @param string $subscription
     * @return Subscriber
     */
    public function getByUserAndSubscription(int $userId, string $subscription)
    {
        return $this->map($this->database->table(Subscriber::TABLE)
                    ->where("user_id", $userId)
                    ->where("subscription", $subscription)->fetch());
    }

    /**
     * Flush (send) all push notification in queue
     * @return void
     */
    public function flush()
    {
        $this->webPush->flush();
    }

    protected function getClassName(): string
    {
        return Subscriber::class;
    }

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
    public function notifyUser(PushNotification $notification, int $userId)
    {
        try {
            foreach ($this->getList() as $subscriber) {
                /* @var $subscriber Subscriber */
                if ($userId !== $subscriber->getUserId()) {
                    continue;
                }
                $this->isQueue = true;
                $report = $this->webPush->sendOneNotification(
                    Subscription::create(\json_decode($subscriber->getSubscription(), true)), // subscription
                    \json_encode($notification->jsonSerialize()) // payload
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
