<?php

namespace Tymy\Module\Team\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Team\Mapper\TeamMapper;

/**
 * Description of Team
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class Team extends BaseModel
{
    public const TABLE = "teams";
    public const TABLE_FEATURES = "features";
    public const MODULE = "team";

    /** @var string */
    private $sysName;

    /** @var string */
    private $name;

    /** @var string */
    private $dbName;

    /** @var string[] */
    private $languages;

    /** @var string */
    private $defaultLanguageCode;

    /** @var string */
    private $sport;

    /** @var string[] */
    private $modules;

    /** @var string */
    private $accountNumber;

    /** @var string */
    private $web;

    /** @var int */
    private $countryId;

    /** @var string */
    private $attendEmail;

    /** @var string */
    private $excuseMail;

    /** @var int */
    private $maxUsers;

    /** @var int */
    private $maxEventsMonth;

    /** @var bool */
    private $advertisement;

    /** @var Datetime */
    private $insertDate;

    /** @var int */
    private $timeZone;

    /** @var string */
    private $dstFlag;

    /** @var string */
    private $appVersion;

    /** @var bool */
    private $useNamedays;

    /** @var DateTime */
    private $tariffUntil;

    /** @var string */
    private $tariffPayment;

    /** @var string */
    private $attCheckType;

    /** @var int */
    private $attendanceCheckDays;

    /** @var string */
    private $host;

    /** @var string */
    private $tariff;

    /** @var array */
    private $features;

    public function getSysName()
    {
        return $this->sysName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function getDefaultLanguageCode()
    {
        return $this->defaultLanguageCode;
    }

    public function getSport()
    {
        return $this->sport;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function getWeb()
    {
        return $this->web;
    }

    public function getCountryId()
    {
        return $this->countryId;
    }

    public function getAttendEmail()
    {
        return $this->attendEmail;
    }

    public function getExcuseMail()
    {
        return $this->excuseMail;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function getMaxEventsMonth()
    {
        return $this->maxEventsMonth;
    }

    public function getAdvertisement()
    {
        return $this->advertisement;
    }

    public function getInsertDate(): Datetime
    {
        return $this->insertDate;
    }

    public function getTimeZone()
    {
        return $this->timeZone;
    }

    public function getDstFlag()
    {
        return $this->dstFlag;
    }

    public function getAppVersion()
    {
        return $this->appVersion;
    }

    public function getUseNamedays()
    {
        return $this->useNamedays;
    }

    public function getTariffUntil(): DateTime
    {
        return $this->tariffUntil;
    }

    public function getTariffPayment()
    {
        return $this->tariffPayment;
    }

    public function getAttCheckType()
    {
        return $this->attCheckType;
    }

    public function getAttendanceCheckDays()
    {
        return $this->attendanceCheckDays;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getTariff()
    {
        return $this->tariff;
    }

    public function setSysName($sysName)
    {
        $this->sysName = $sysName;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    public function setLanguages($languages)
    {
        $this->languages = is_string($languages) ? explode(",", $languages) : $languages;
        return $this;
    }

    public function setDefaultLanguageCode($defaultLanguageCode)
    {
        $this->defaultLanguageCode = $defaultLanguageCode;
        return $this;
    }

    public function setSport($sport)
    {
        $this->sport = $sport;
        return $this;
    }

    public function setModules($modules)
    {
        $this->modules = is_string($modules) ? explode(",", $modules) : $modules;
        return $this;
    }

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function setWeb($web)
    {
        $this->web = $web;
        return $this;
    }

    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
        return $this;
    }

    public function setAttendEmail($attendEmail)
    {
        $this->attendEmail = $attendEmail;
        return $this;
    }

    public function setExcuseMail($excuseMail)
    {
        $this->excuseMail = $excuseMail;
        return $this;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
        return $this;
    }

    public function setMaxEventsMonth($maxEventsMonth)
    {
        $this->maxEventsMonth = $maxEventsMonth;
        return $this;
    }

    public function setAdvertisement($advertisement)
    {
        $this->advertisement = $advertisement;
        return $this;
    }

    public function setInsertDate(Datetime $insertDate)
    {
        $this->insertDate = $insertDate;
        return $this;
    }

    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    public function setDstFlag($dstFlag)
    {
        $this->dstFlag = $dstFlag;
        return $this;
    }

    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
        return $this;
    }

    public function setUseNamedays($useNamedays)
    {
        $this->useNamedays = $useNamedays;
        return $this;
    }

    public function setTariffUntil(DateTime $tariffUntil)
    {
        $this->tariffUntil = $tariffUntil;
        return $this;
    }

    public function setTariffPayment($tariffPayment)
    {
        $this->tariffPayment = $tariffPayment;
        return $this;
    }

    public function setAttCheckType($attCheckType)
    {
        $this->attCheckType = $attCheckType;
        return $this;
    }

    public function setAttendanceCheckDays($attendanceCheckDays)
    {
        $this->attendanceCheckDays = $attendanceCheckDays;
        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function setTariff($tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function setFeatures($features)
    {
        $this->features = $features;
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
