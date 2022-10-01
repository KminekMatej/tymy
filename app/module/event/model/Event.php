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
    public const PAGING_EVENTS_PER_PAGE = 15;

    private string $caption;
    private DateTime $created;
    private ?int $createdUserId = null;
    private int $eventTypeId;
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
    private bool $canPlanOthers = false;
    private bool $canResult = false;
    private bool $inPast = false;
    private bool $inFuture = false;
    private ?Attendance $myAttendance = null;
    private array $attendance = [];
    private bool $attendancePending = false;
    private string $backgroundColor = 'blue';
    private string $borderColor = 'blue';
    private string $textColor = 'blue';
    private EventType $eventType;

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getCreatedUserId(): ?int
    {
        return $this->createdUserId;
    }

    public function getEventTypeId(): int
    {
        return $this->eventTypeId;
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

    public function getCanPlanOthers(): bool
    {
        return $this->canPlanOthers;
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

    /**
     * @return mixed[]
     */
    public function getAttendance(): array
    {
        return $this->attendance;
    }

    public function getAttendancePending(): bool
    {
        return $this->attendancePending;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function getBorderColor(): string
    {
        return $this->borderColor;
    }

    public function getTextColor(): string
    {
        return $this->textColor;
    }

    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    public function setCaption(string $caption): static
    {
        $this->caption = $caption;
        return $this;
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

    public function setEventTypeId(int $eventTypeId): static
    {
        $this->eventTypeId = $eventTypeId;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setCloseTime(DateTime $closeTime): static
    {
        $this->closeTime = $closeTime;
        return $this;
    }

    public function setStartTime(DateTime $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function setEndTime(DateTime $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function setPlace(?string $place): static
    {
        $this->place = $place;
        return $this;
    }

    public function setViewRightName(?string $viewRightName): static
    {
        $this->viewRightName = $viewRightName;
        return $this;
    }

    public function setPlanRightName(?string $planRightName): static
    {
        $this->planRightName = $planRightName;
        return $this;
    }

    public function setResultRightName(?string $resultRightName): static
    {
        $this->resultRightName = $resultRightName;
        return $this;
    }

    public function setWebName(?string $webName): static
    {
        $this->webName = $webName;
        return $this;
    }

    public function setCanView(bool $canView): static
    {
        $this->canView = $canView;
        return $this;
    }

    public function setCanPlan(bool $canPlan): static
    {
        $this->canPlan = $canPlan;
        return $this;
    }

    public function setCanPlanOthers(bool $canPlanOthers): static
    {
        $this->canPlanOthers = $canPlanOthers;
        return $this;
    }

    public function setCanResult(bool $canResult): static
    {
        $this->canResult = $canResult;
        return $this;
    }

    public function setInPast(bool $inPast): static
    {
        $this->inPast = $inPast;
        return $this;
    }

    public function setInFuture(bool $inFuture): static
    {
        $this->inFuture = $inFuture;
        return $this;
    }

    public function setMyAttendance(?Attendance $myAttendance): static
    {
        $this->myAttendance = $myAttendance;
        return $this;
    }

    /**
     * @param mixed[] $attendance
     */
    public function setAttendance(array $attendance): void
    {
        $this->attendance = $attendance;
    }

    public function setAttendancePending(bool $attendancePending): void
    {
        $this->attendancePending = $attendancePending;
    }

    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    public function setBorderColor(string $borderColor): void
    {
        $this->borderColor = $borderColor;
    }

    public function setTextColor(string $textColor): void
    {
        $this->textColor = $textColor;
    }

    public function setEventType(EventType $eventType): static
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function addAttendance(Attendance $attendance): static
    {
        $this->attendance[] = $attendance;
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
        return EventMapper::scheme();
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
        $json = parent::jsonSerialize() + [
            "type" => $this->getType(),
            "attendance" => $this->arrayToJson(array_values($this->getAttendance())),
            "canView" => $this->getCanView(),
            "canPlan" => $this->getCanPlan(),
            "canPlanOthers" => $this->getCanPlanOthers(),
            "canResult" => $this->getCanResult(),
            "inPast" => $this->getInPast(),
            "inFuture" => $this->getInFuture(),
            "eventType" => $this->getEventType()->jsonSerialize(),
        ];

        if ($this->getMyAttendance() !== null) {   //set myAttendance property only if there is some
            $json += ["myAttendance" => $this->getMyAttendance()->jsonSerialize()];
        }
        return $json;
    }
}
