<?php

namespace Tymy\Module\Event\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Mapper\EventMapper;

/**
 * Description of Event
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 19. 9. 2020
 */
class Event extends BaseModel
{
    public const TABLE = "events";
    public const MODULE = "event";

    private string $caption;
    private string $type;
    private ?string $description = null;
    private DateTime $closeTime;
    private DateTime $startTime;
    private DateTime $endTime;
    private ?string $link = null;
    private ?string $place = null;
    private ?string $viewRightName = null;
    private ?string $planRightName = null;
    private ?string $resultRightName = null;
    private ?string $webName = null;
    private bool $canView = true;
    private bool $canPlan = false;
    private bool $canResult = false;
    private bool $inPast = false;
    private bool $inFuture = false;
    private ?Attendance $myAttendance = null;
    private bool $attendancePending = false;

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCloseTime(): DateTime
    {
        return $this->closeTime;
    }

    public function getStartTime(): DateTime
    {
        return $this->startTime;
    }

    public function getEndTime(): DateTime
    {
        return $this->endTime;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function getViewRightName(): ?string
    {
        return $this->viewRightName;
    }

    public function getPlanRightName(): ?string
    {
        return $this->planRightName;
    }

    public function getResultRightName(): ?string
    {
        return $this->resultRightName;
    }

    public function getWebName(): ?string
    {
        return $this->webName;
    }

    public function getCanView(): bool
    {
        return $this->canView;
    }

    public function getCanPlan(): bool
    {
        return $this->canPlan;
    }

    public function getCanResult(): bool
    {
        return $this->canResult;
    }

    public function getInPast(): bool
    {
        return $this->inPast;
    }

    public function getInFuture(): bool
    {
        return $this->inFuture;
    }

    public function getMyAttendance(): ?Attendance
    {
        return $this->myAttendance;
    }

    public function getAttendancePending(): bool
    {
        return $this->attendancePending;
    }

    public function setCaption(string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function setCloseTime(DateTime $closeTime)
    {
        $this->closeTime = $closeTime;
        return $this;
    }

    public function setStartTime(DateTime $startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function setEndTime(DateTime $endTime)
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function setLink(?string $link)
    {
        $this->link = $link;
        return $this;
    }

    public function setPlace(?string $place)
    {
        $this->place = $place;
        return $this;
    }

    public function setViewRightName(?string $viewRightName)
    {
        $this->viewRightName = $viewRightName;
        return $this;
    }

    public function setPlanRightName(?string $planRightName)
    {
        $this->planRightName = $planRightName;
        return $this;
    }

    public function setResultRightName(?string $resultRightName)
    {
        $this->resultRightName = $resultRightName;
        return $this;
    }

    public function setWebName(?string $webName)
    {
        $this->webName = $webName;
        return $this;
    }

    public function setCanView(bool $canView)
    {
        $this->canView = $canView;
        return $this;
    }

    public function setCanPlan(bool $canPlan)
    {
        $this->canPlan = $canPlan;
        return $this;
    }

    public function setCanResult(bool $canResult)
    {
        $this->canResult = $canResult;
        return $this;
    }

    public function setInPast(bool $inPast)
    {
        $this->inPast = $inPast;
        return $this;
    }

    public function setInFuture(bool $inFuture)
    {
        $this->inFuture = $inFuture;
        return $this;
    }

    public function setMyAttendance(?Attendance $myAttendance)
    {
        $this->myAttendance = $myAttendance;
        return $this;
    }

    public function setAttendancePending(bool $attendancePending): void
    {
        $this->attendancePending = $attendancePending;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return EventMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize() + [
            "canView" => $this->getCanView(),
            "canPlan" => $this->getCanPlan(),
            "canResult" => $this->getCanResult(),
            "inPast" => $this->getInPast(),
            "inFuture" => $this->getInFuture(),
        ];

        if ($this->getMyAttendance()) {   //set myAttendance property only if there is some
            $json = $json + ["myAttendance" => $this->getMyAttendance()->jsonSerialize()];
        }
        return $json;
    }
}