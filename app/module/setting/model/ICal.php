<?php

namespace Tymy\Module\Settings\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\ICalMapper;

/**
 * Description of ICal
 *
 * @author kminekmatej, 11. 9. 2022, 17:02:32
 */
class ICal extends BaseModel
{
    public const MODULE = "settings";
    public const TABLE = "ical";

    private DateTime $created;
    private ?int $createdUserId = null;
    private int $userId;
    private string $hash;
    private bool $enabled;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getCreatedUserId(): ?int
    {
        return $this->createdUserId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setCreated(DateTime $created)
    {
        $this->created = $created;
        return $this;
    }

    public function setCreatedUserId(?int $createdUserId)
    {
        $this->createdUserId = $createdUserId;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setHash(string $hash)
    {
        $this->hash = $hash;
        return $this;
    }

    public function setEnabled(?bool $enabled)
    {
        $this->enabled = $enabled ? true : false;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return ICalMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
