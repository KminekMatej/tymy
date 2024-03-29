<?php

namespace Tymy\Module\User\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\User\Mapper\InvitationMapper;

/**
 * Description of Invitation
 *
 * @author kminekmatej, 25. 9. 2022, 21:18:30
 */
class Invitation extends BaseModel
{
    public const MODULE = "user";
    public const TABLE = "user_invitation";
    public const STATUS_SENT = "sent";
    public const STATUS_ACCEPTED = "accepted";
    public const STATUS_EXPIRED = "expired";

    private DateTime $created;
    private ?int $createdUserId = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $email = null;
    private string $code;
    private string $lang;
    private ?int $userId = null;
    private DateTime $validUntil;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getCreatedUserId(): ?int
    {
        return $this->createdUserId;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }

    public function setCreated(DateTime $created): static
    {
        $this->created = $created;
        return $this;
    }

    public function setCreatedUserId(?int $createdUserId): static
    {
        $this->createdUserId = $createdUserId;
        return $this;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function setLang(string $lang): static
    {
        $this->lang = $lang;
        return $this;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function setValidUntil(DateTime $validUntil): static
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    public function getStatus(): string
    {
        if ($this->userId) {
            return self::STATUS_ACCEPTED;
        }
        return $this->getValidUntil() < (new DateTime()) ? self::STATUS_EXPIRED : self::STATUS_SENT;
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
        return InvitationMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
