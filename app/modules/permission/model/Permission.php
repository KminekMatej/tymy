<?php

namespace Tymy\Module\Permission\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Mapper\PermissionMapper;

/**
 * Description of Permission
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 8. 2020
 */
class Permission extends BaseModel
{

    public const TABLE = "rights";
    public const MODULE = "permission";
    public const TYPE_USER = "USR";
    public const TYPE_SYSTEM = "SYS";

    private int $id;
    private string $type;
    private string $name;
    private string $webname;
    private ?string $caption = null;
    private ?array $allowedRoles = null;
    private ?array $revokedRoles = null;
    private ?array $allowedStatuses = null;
    private ?array $revokedStatuses = null;
    private ?array $allowedUsers = null;
    private ?array $revokedUsers = null;
    private ?DateTime $updatedAt = null;
    private ?int $updatedById = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWebname(): string
    {
        return $this->webname;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getAllowedRoles(): ?array
    {
        return $this->allowedRoles;
    }

    public function getRevokedRoles(): ?array
    {
        return $this->revokedRoles;
    }

    public function getAllowedStatuses(): ?array
    {
        return $this->allowedStatuses;
    }

    public function getRevokedStatuses(): ?array
    {
        return $this->revokedStatuses;
    }

    public function getAllowedUsers(): ?array
    {
        return $this->allowedUsers;
    }

    public function getRevokedUsers(): ?array
    {
        return $this->revokedUsers;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setWebname(string $webname): void
    {
        $this->webname = $webname;
    }

    public function setCaption(?string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setAllowedRoles(?string $allowedRoles)
    {
        $this->allowedRoles = empty($allowedRoles) ? null : explode(",", $allowedRoles);
        return $this;
    }

    public function setRevokedRoles(?string $revokedRoles)
    {
        $this->revokedRoles = empty($revokedRoles) ? null : explode(",", $revokedRoles);
        return $this;
    }

    public function setAllowedStatuses(?string $allowedStatuses)
    {
        $this->allowedStatuses = empty($allowedStatuses) ? null : explode(",", $allowedStatuses);
        return $this;
    }

    public function setRevokedStatuses(?string $revokedStatuses)
    {
        $this->revokedStatuses = empty($revokedStatuses) ? null : explode(",", $revokedStatuses);
        return $this;
    }

    public function setAllowedUsers(?string $allowedUsers)
    {
        $this->allowedUsers = empty($allowedUsers) ? null : array_map('intval', explode(",", $allowedUsers));
        return $this;
    }

    public function setRevokedUsers(?string $revokedUsers)
    {
        $this->revokedUsers = empty($revokedUsers) ? null : array_map('intval', explode(",", $revokedUsers));
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUpdatedById(?int $updatedById)
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return PermissionMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

}