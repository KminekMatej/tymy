<?php

namespace Tymy\Module\PushNotification\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\PushNotification\Mapper\SubscriberMapper;

class Subscriber extends BaseModel
{
    const MODULE = "push-notification";
    const TABLE = "push_notification";
    const TYPE_WEB = "WEB";
    const TYPE_APNS = "APNS";
    const TYPE_FCM = "FCM";

    private string $type;
    private int $userId;
    private string $subscription;
    private DateTime $created;

    public function getType(): string
    {
        return $this->type;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSubscription(): string
    {
        return $this->subscription;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setSubscription(string $subscription)
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function setCreated(DateTime $created)
    {
        $this->created = $created;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return SubscriberMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
