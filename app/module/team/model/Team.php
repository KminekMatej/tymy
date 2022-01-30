<?php
namespace Tymy\Module\Team\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Mapper\TeamMapper;

/**
 * Description of Team
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class Team extends BaseModel
{

    public const TABLE = "teams";
    public const MODULE = "team";

    private string $sysName;
    private string $name;
    private string $dbName;

    /** @var string[] */
    private array $languages;
    private string $defaultLanguageCode;
    private ?string $sport = null;
    private ?string $accountNumber = null;
    private ?string $web = null;
    private ?int $countryId = null;
    private int $maxUsers;
    private int $maxEventsMonth;
    private bool $advertisement;
    private Datetime $insertDate;
    private int $timeZone;
    private string $dstFlag;
    private ?DateTime $tariffUntil = null;
    private ?string $tariffPayment = null;
    private string $attCheckType;
    private int $attendanceCheckDays;
    private string $tariff;
    private string $skin;
    private array $requiredFields = [];

    public function getSysName(): string
    {
        return $this->sysName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getDefaultLanguageCode(): string
    {
        return $this->defaultLanguageCode;
    }

    public function getSport(): ?string
    {
        return $this->sport;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    public function getMaxUsers(): int
    {
        return $this->maxUsers;
    }

    public function getMaxEventsMonth(): int
    {
        return $this->maxEventsMonth;
    }

    public function getAdvertisement(): bool
    {
        return $this->advertisement;
    }

    public function getInsertDate(): Datetime
    {
        return $this->insertDate;
    }

    public function getTimeZone(): int
    {
        return $this->timeZone;
    }

    public function getDstFlag(): string
    {
        return $this->dstFlag;
    }

    public function getTariffUntil(): ?DateTime
    {
        return $this->tariffUntil;
    }

    public function getTariffPayment(): ?string
    {
        return $this->tariffPayment;
    }

    public function getAttCheckType(): string
    {
        return $this->attCheckType;
    }

    public function getAttendanceCheckDays(): int
    {
        return $this->attendanceCheckDays;
    }

    public function getTariff(): string
    {
        return $this->tariff;
    }

    public function getSkin(): string
    {
        return $this->skin ?? TeamManager::DEFAULT_SKIN;
    }
    
    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    
    public function setLanguages($languages)
    {
        $this->languages = is_string($languages) ? explode(",", $languages) : $languages;
        return $this;
    }

    public function setSysName(string $sysName)
    {
        $this->sysName = $sysName;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    public function setDefaultLanguageCode(string $defaultLanguageCode)
    {
        $this->defaultLanguageCode = $defaultLanguageCode;
        return $this;
    }

    public function setSport(?string $sport)
    {
        $this->sport = $sport;
        return $this;
    }

    public function setAccountNumber(?string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function setWeb(?string $web)
    {
        $this->web = $web;
        return $this;
    }

    public function setCountryId(?int $countryId)
    {
        $this->countryId = $countryId;
        return $this;
    }

    public function setMaxUsers(int $maxUsers)
    {
        $this->maxUsers = $maxUsers;
        return $this;
    }

    public function setMaxEventsMonth(int $maxEventsMonth)
    {
        $this->maxEventsMonth = $maxEventsMonth;
        return $this;
    }

    public function setAdvertisement(bool $advertisement)
    {
        $this->advertisement = $advertisement;
        return $this;
    }

    public function setInsertDate(Datetime $insertDate)
    {
        $this->insertDate = $insertDate;
        return $this;
    }

    public function setTimeZone(int $timeZone)
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    public function setDstFlag(string $dstFlag)
    {
        $this->dstFlag = $dstFlag;
        return $this;
    }

    public function setTariffUntil(?DateTime $tariffUntil)
    {
        $this->tariffUntil = $tariffUntil;
        return $this;
    }

    public function setTariffPayment(?string $tariffPayment)
    {
        $this->tariffPayment = $tariffPayment;
        return $this;
    }

    public function setAttCheckType(string $attCheckType)
    {
        $this->attCheckType = $attCheckType;
        return $this;
    }

    public function setAttendanceCheckDays(int $attendanceCheckDays)
    {
        $this->attendanceCheckDays = $attendanceCheckDays;
        return $this;
    }

    public function setTariff(string $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    public function setSkin(string $skin)
    {
        $this->skin = $skin;
        return $this;
    }

    public function setRequiredFields(string $requiredFields)
    {
        $this->requiredFields = explode(",", $requiredFields);
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return TeamMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
