<?php
namespace Tymy\Module\User\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Mapper\UserMapper;

/**
 * Description of User
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 8. 2020
 */
class User extends BaseModel
{

    public const TABLE = "users";
    public const TABLE_MAILS = "usr_mails";
    public const TABLE_PWD_RESET = "pwd_reset";
    public const VIEW = "v_users";
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
    private ?string $displayName = null;
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

    public function getDisplayName(): ?string
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

    public function setLogin(string $login)
    {
        $this->login = $login;
        return $this;
    }

    public function setCanLogin(string $canLogin)
    {
        $this->canLogin = $canLogin == "YES" ? true : false;
        return $this;
    }

    public function setCanEditCallName(string $canEditCallName)
    {
        $this->canEditCallName = $canEditCallName == "YES" ? true : false;
        return $this;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setLastLogin(?DateTime $lastLogin)
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setRoles(string $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    public function setFirstName(?string $firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setCallName(?string $callName)
    {
        $this->callName = $callName;
        return $this;
    }

    public function setLanguage(?string $language)
    {
        $this->language = $language;
        return $this;
    }

    public function setEmail(?string $email)
    {
        $this->email = $email;
        return $this;
    }

    public function setJerseyNumber(?string $jerseyNumber)
    {
        $this->jerseyNumber = $jerseyNumber;
        return $this;
    }

    public function setGender(?string $gender)
    {
        $this->gender = strtoupper($gender);
        return $this;
    }

    public function setStreet(?string $street)
    {
        $this->street = $street;
        return $this;
    }

    public function setCity(?string $city)
    {
        $this->city = $city;
        return $this;
    }

    public function setZipCode(?string $zipCode)
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setPhone2(?string $phone2)
    {
        $this->phone2 = $phone2;
        return $this;
    }

    public function setDisplayName(?string $displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function setBirthDate(?DateTime $birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function setNameDayMonth(int $nameDayMonth)
    {
        $this->nameDayMonth = $nameDayMonth;
        return $this;
    }

    public function setNameDayDay(int $nameDayDay)
    {
        $this->nameDayDay = $nameDayDay;
        return $this;
    }

    public function setAccountNumber(?string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function setBirthCode(?string $birthCode)
    {
        $this->birthCode = $birthCode;
        return $this;
    }

    public function setGdprAccepted(?DateTime $gdprAccepted)
    {
        $this->gdprAccepted = $gdprAccepted;
        return $this;
    }

    public function setGdprRevoked(?DateTime $gdprRevoked)
    {
        $this->gdprRevoked = $gdprRevoked;
        return $this;
    }

    public function setLastReadNews(?DateTime $lastReadNews)
    {
        $this->lastReadNews = $lastReadNews;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    public function getPictureUrl(): string
    {
        return $this->pictureUrl;
    }

    public function setPictureUrl(string $pictureUrl)
    {
        $this->pictureUrl = $pictureUrl;
        return $this;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): User
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function getGhost(): bool
    {
        return $this->ghost;
    }

    public function setGhost(bool $ghost)
    {
        $this->ghost = $ghost;
        return $this;
    }

    public function setWarnings(int $warnings): self
    {
        $this->warnings = $warnings;
        return $this;
    }

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

    public function setSkin(?string $skin = null)
    {
        $this->skin = $skin;
        return $this;
    }

    public function addErrField(string $fieldName)
    {
        $this->errFields[] = $fieldName;
        $this->warnings++;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return UserMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        unset($data["password"]);
        $data["birthDate"] = $this->getBirthDate() ? $this->getBirthDate()->format(self::DATE_ENG_FORMAT) : null;

        return $data + [
            "email" => $this->getEmail(),
            "fullName" => $this->getFullName(),
            "rolesAsString" => join(",", $this->getRoles()),
            "pictureUrl" => $this->getPictureUrl(),
            "displayName" => $this->getDisplayName(),
        ];
    }
}
