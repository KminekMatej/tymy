<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Bridges\ApplicationLatte\Template;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;

/**
 * Description of EventBasePresenter
 */
class EventBasePresenter extends SecuredPresenter
{
    #[\Nette\DI\Attributes\Inject]
    public EventTypeManager $eventTypeManager;

    #[\Nette\DI\Attributes\Inject]
    public StatusManager $statusManager;

    #[\Nette\DI\Attributes\Inject]
    public AttendanceManager $attendanceManager;

    public function beforeRender()
    {
        parent::beforeRender();

        $this->addBreadcrumb($this->translator->translate("event.attendance", 2), $this->link(":Event:Default:"));

        assert($this->template instanceof Template);
        $this->template->addFilter('genderTranslate', function ($gender) {
            switch ($gender) {
                case "MALE":
                    return $this->translator->translate("team.male", 2);
                case "FEMALE":
                    return $this->translator->translate("team.female", 2);
                case "UNKNOWN":
                    return $this->translator->translate("team.unknownSex");
            }
        });

        $this->template->addFilter("prestatusClass", function (?Attendance $myAttendance, $statusId, $canPlan, $startTime) {
            $myPreStatusId = $myAttendance !== null ? $myAttendance->getPreStatusId() : null;
            $myPostStatusId = $myAttendance !== null ? $myAttendance->getPostStatusId() : null;

            if (!$canPlan) {
                return $statusId == $myPostStatusId && $myPostStatusId ? "statusBtn$statusId disabled active" : "btn-outline-secondary disabled";
            }
            if (strtotime($startTime) > strtotime(date("c"))) { // pokud podminka plati, akce je budouci
                return $statusId == $myPreStatusId ? "statusBtn$statusId active" : "statusBtn$statusId";
            } elseif (is_null($myPostStatusId)) { // akce uz byla, post status nevyplnen
                return $statusId == $myPreStatusId && !is_null($myPreStatusId) ? "statusBtn$statusId disabled active" : "btn-outline-secondary disabled";
            } else {
                return $statusId == $myPostStatusId ? "statusBtn$statusId disabled active" : "btn-outline-secondary disabled";
            }
        });

        $this->template->statusList = $this->statusManager->getIdList();
    }

    /**
     * Transform array of events into event feed - array in format specified by FullCalendar specifications
     *
     * @param Event[] $events
     */
    protected function toFeed(array $events): array
    {
        $feed = [];

        foreach ($events as $event) {
            assert($event instanceof Event);
            $feed[] = [
                "id" => $event->getId(),
                "title" => $event->getCaption(),
                "start" => $event->getStartTime()->format(BaseModel::DATETIME_ISO_FORMAT),
                "end" => $event->getEndTime()->format(BaseModel::DATETIME_ISO_FORMAT),
                "backgroundColor" => $event->getBackgroundColor(),
                "borderColor" => $event->getBorderColor(),
                "textColor" => $event->getTextColor(),
                "url" => $this->link(":Event:Detail:", $event->getWebName()),
            ];
        }

        return $feed;
    }

    public function handleAttendance(int $eventId, int $preStatusId, ?string $desc = null, ?int $userId = null): void
    {
        $this->attendanceManager->create([
            "userId" => $userId ?: $this->user->getId(),
            "eventId" => $eventId,
            "preStatusId" => $preStatusId,
            "preDescription" => $desc
        ]);

        if ($this->isAjax()) {
            $this->redrawControl("attendanceWarning");
            $this->redrawControl("attendanceTabs");
            $this->redrawNavbar();
        }
    }
}
