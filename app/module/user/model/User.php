<?php

namespace Tymy\Module\User\Model;

use Nette\Utils\Arrays;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Mapper\UserMapper;

/**
 * Description of User
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 8. 2020
 */
class User extends BaseModel
{
    public const TABLE = "user";
    public const TABLE_MAILS = "usr_mails";
    public const TABLE_PWD_RESET = "pwd_reset";
    public const MODULE = "user";
    public const ROLE_SUPER = "SUPER";
    public const ROLE_USER = "USR";
    public const ROLE_WEB = "WEB";
    public const ROLE_ATTENDANCE = "ATT";
    public const ROLE_SUPER_CLASS = "success";
    public const ROLE_USER_CLASS = "info";
    public const ROLE_ATTENDANCE_CLASS = "warning";
    public const STATUS_MEMBER = "MEMBER";
    public const STATUS_DELETED = "DELETED";
    public const STATUS_PLAYER = "PLAYER";
    public const STATUS_SICK = "SICK";
    public const STATUS_INIT = "INIT";
    public const FIELDS_PERSONAL = ["gender", "firstName", "lastName", "phone", "email", "birthDate", "nameDayMonth", "nameDayDay", "language"];
    public const FIELDS_LOGIN = ["callName", "canEditCallName", "login", "password", "canLogin"];
    public const FIELDS_TEAMINFO = ["status", "jerseyNumber"];
    public const FIELDS_ADDRESS = ["street", "city", "zipCode"];

    private string $login;
    private bool $canLogin = false;
    private bool $canEditCallName = false;
    private DateTime $createdAt;
    private ?DateTime $lastLogin = null;
    private string $status;
    private ?string $roles = null;
    private bool $ghost = false;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private string $fullName;
    private string $password;
    private ?string $callName = null;
    private ?string $language = null;
    private ?string $email = null;
    private ?string $jerseyNumber = null;
    private ?string $gender = null;
    private ?string $street = null;
    private ?string $city = null;
    private ?string $zipCode = null;
    private ?string $phone = null;
    private ?string $phone2 = null;
    private ?DateTime $birthDate = null;
    private int $nameDayMonth = 0;
    private int $nameDayDay = 0;
    private ?string $accountNumber = null;
    private ?string $birthCode = null;
    private ?DateTime $gdprAccepted = null;
    private ?DateTime $gdprRevoked = null;
    private ?DateTime $lastReadNews = null;
    private string $pictureUrl;
    private bool $isNew = false;
    private int $warnings = 0;
    private array $errFields = [];
    private ?string $webName = null;
    private ?string $skin = null;
    private bool $hideDiscDesc = false;
    private bool $hasBirthdayToday = false;
    private int $yearsOld;
    private bool $hasBirthdayTommorow = false;
    private bool $hasNamedayToday = false;
    private bool $hasNamedayTommorow = false;

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getCanLogin(): bool
    {
        return $this->canLogin;
    }

    public function getCanEditCallName(): bool
    {
        return $this->canEditCallName;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return mixed[]
     */
    public function getRoles(): array
    {
        return empty($this->roles) ? [] : explode(",", $this->roles);
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCallName(): ?string
    {
        return $this->callName;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getJerseyNumber(): ?string
    {
        return $this->jerseyNumber;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function getDisplayName(): string
    {
        if (!empty($this->callName)) {
            return $this->callName;
        }

        if (!empty($this->getFullName())) {
            return $this->getFullName();
        }

        return $this->getLogin();
        ;
    }

    public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    public function getNameDayMonth(): int
    {
        return $this->nameDayMonth;
    }

    public function getNameDayDay(): int
    {
        return $this->nameDayDay;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function getBirthCode(): ?string
    {
        return $this->birthCode;
    }

    public function getGdprAccepted(): ?DateTime
    {
        return $this->gdprAccepted;
    }

    public function getGdprRevoked(): ?DateTime
    {
        return $this->gdprRevoked;
    }

    public function getLastReadNews(): ?DateTime
    {
        return $this->lastReadNews;
    }

    public function getWarnings(): int
    {
        return $this->warnings;
    }

    /**
     * @return mixed[]
     */
    public function getErrFields(): array
    {
        return $this->errFields;
    }

    public function getWebName(): ?string
    {
        return $this->webName;
    }

    public function getSkin(): string
    {
        return $this->skin ?: TeamManager::DEFAULT_SKIN;
    }

    public function getHideDiscDesc(): bool
    {
        return $this->hideDiscDesc;
    }

    public function getHasBirthdayToday(): bool
    {
        return $this->hasBirthdayToday;
    }

    public function getHasBirthdayTommorow(): bool
    {
        return $this->hasBirthdayTommorow;
    }

    public function getHasNamedayToday(): bool
    {
        return $this->hasNamedayToday;
    }

    public function getHasNamedayTommorow(): bool
    {
        return $this->hasNamedayTommorow;
    }

    public function getYearsOld(): int
    {
        return $this->yearsOld;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function setCanLogin($canLogin): static
    {
        $this->canLogin = (bool) $canLogin;
        return $this;
    }

    public function setCanEditCallName($canEditCallName): static
    {
        $this->canEditCallName = (bool) $canEditCallName;
        return $this;
    }

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setLastLogin(?DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function setRoles(string $roles): static
    {
        $this->roles = $roles;
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

    public function setCallName(?string $callName): static
    {
        $this->callName = $callName;
        return $this;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function setJerseyNumber(?string $jerseyNumber): static
    {
        $this->jerseyNumber = $jerseyNumber;
        return $this;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = strtoupper($gender);
        return $this;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;
        return $this;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function setPhone2(?string $phone2): static
    {
        $this->phone2 = $phone2;
        return $this;
    }

    public function setDisplayName(?string $displayName): static
    {
        return $this;
    }

    public function setBirthDate(?DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;
        $now = new DateTime();

        if ($birthDate) {
            $this->hasBirthdayToday = $birthDate->format("m-d") == $now->format("m-d");
            $this->hasBirthdayTommorow = $birthDate->modifyClone("- 1 day")->format("m-d") == $now->format("m-d");
        }

        $this->yearsOld = intval($now->format("Y")) - intval($birthDate->format("Y"));

        return $this;
    }

    public function setNameDayMonth(int $nameDayMonth): static
    {
        $this->nameDayMonth = $nameDayMonth;
        $this->setHasNameday();
        return $this;
    }

    public function setNameDayDay(int $nameDayDay): static
    {
        $this->nameDayDay = $nameDayDay;
        $this->setHasNameday();
        return $this;
    }

    public function setHasNameday()
    {
        if (!isset($this->nameDayDay, $this->nameDayMonth)) {
            return;
        }

        $now = new DateTime();
        $nameDay = new DateTime(date('Y') . "-{$this->nameDayMonth}-{$this->nameDayDay}");
        $this->hasNamedayToday = $nameDay->format("m-d") == $now->format("m-d");
        $this->hasNamedayTommorow = $nameDay->modifyClone("- 1 day")->format("m-d") == $now->format("m-d");

        return $this;
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function setBirthCode(?string $birthCode): static
    {
        $this->birthCode = $birthCode;
        return $this;
    }

    public function setGdprAccepted(?DateTime $gdprAccepted): static
    {
        $this->gdprAccepted = $gdprAccepted;
        return $this;
    }

    public function setGdprRevoked(?DateTime $gdprRevoked): static
    {
        $this->gdprRevoked = $gdprRevoked;
        return $this;
    }

    public function setLastReadNews(?DateTime $lastReadNews): static
    {
        $this->lastReadNews = $lastReadNews;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPictureUrl(): string
    {
        return $this->pictureUrl;
    }

    public function setPictureUrl(string $pictureUrl): static
    {
        $this->pictureUrl = $pictureUrl;
        return $this;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): static
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function getGhost(): bool
    {
        return $this->ghost;
    }

    public function setGhost(bool $ghost): static
    {
        $this->ghost = $ghost;
        return $this;
    }

    public function setWarnings(int $warnings): self
    {
        $this->warnings = $warnings;
        return $this;
    }

    /**
     * @param mixed[] $errFields
     */
    public function setErrFields(array $errFields): self
    {
        $this->errFields = $errFields;
        return $this;
    }

    public function setWebName(?string $webName): self
    {
        $this->webName = $webName;
        return $this;
    }

    public function setSkin(?string $skin = null): static
    {
        $this->skin = $skin;
        return $this;
    }

    public function setHideDiscDesc(int $hideDiscDesc): static
    {
        $this->hideDiscDesc = (bool) $hideDiscDesc;
        return $this;
    }

    public function addErrField(string $fieldName): void
    {
        $this->errFields[] = $fieldName;
        $this->warnings++;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    /**
     * @return Field[]
     */
    public function getScheme(): array
    {
        return UserMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset($data["password"]);
        unset($data["userName"]);
        $data["birthDate"] = $this->getBirthDate() !== null ? $this->getBirthDate()->format(self::DATE_ENG_FORMAT) : null;

        return $data + [
            "email" => $this->getEmail(),
            "fullName" => $this->getFullName(),
            "rolesAsString" => implode(",", $this->getRoles()),
            "pictureUrl" => $this->getPictureUrl(),
            "displayName" => $this->getDisplayName(),
            "hasBirthdayToday" => $this->hasBirthdayToday,
            "hasBirthdayTommorow" => $this->hasBirthdayTommorow,
            "hasNamedayToday" => $this->hasNamedayToday,
            "hasNamedayTommorow" => $this->hasNamedayTommorow,
        ];
    }

    /**
     * Serialize to one-dimensional array
     * @return array
     */
    public function csvSerialize(): array
    {
        $array = $this->jsonSerialize();

        $array["roles"] = join(",", $array["roles"]);

        return Arrays::flatten($array, true);
    }
}
