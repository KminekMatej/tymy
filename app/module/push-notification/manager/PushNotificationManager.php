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
use Tymy\Module\User\Manager\UserManager;

/**
 * PushNotificationManager is a class for handling read operations upon Push Notification subscription table.
 */
class PushNotificationManager extends BaseManager
{

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
}
