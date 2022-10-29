<?php

namespace Tymy\Module\PushNotification\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\PushNotification\Mapper\SubscriberMapper;

class Subscriber extends BaseModel
{
    public const MODULE = "push-notification";
    public const TABLE = "push_notification";
    public const TYPE_WEB = "WEB";
    public const TYPE_APNS = "APNS";
    public const TYPE_FCM = "FCM";

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

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function setSubscription(string $subscription): static
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function setCreated(DateTime $created): static
    {
        $this->created = $created;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return SubscriberMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
