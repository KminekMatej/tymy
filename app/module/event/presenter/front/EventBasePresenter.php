<?php
namespace Tymy\Module\Event\Presenter\Front;

use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;

/**
 * Description of EventBasePresenter
 *
 * @author kminekmatej
 */
class EventBasePresenter extends SecuredPresenter
{

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;

    /** @inject */
    public AttendanceManager $attendanceManager;

    public function beforeRender()
    {
        parent::beforeRender();

        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("event.attendance", 2), "link" => $this->link(":Event:Default:")]]);

        $this->template->addFilter('genderTranslate', function ($gender) {
            switch ($gender) {
                case "MALE": return $this->translator->translate("team.male", 2);
                case "FEMALE": return $this->translator->translate("team.female", 2);
                case "UNKNOWN": return $this->translator->translate("team.unknownSex");
            }
        });

        $eventTypes = $this->eventTypeManager->getIndexedList();

        $this->template->addFilter("prestatusClass", function (?Attendance $myAttendance, $eventType, $code, $canPlan, $startTime) use ($eventTypes) {
            $myPreStatus = empty($myAttendance) || empty($myAttendance->getPreStatus()) || $myAttendance->getPreStatus() == "UNKNOWN" ? "not-set" : $eventTypes[$eventType]->getPreStatusSet()[$myAttendance->getPreStatus()]->getCode();
            $myPostStatus = empty($myAttendance) || empty($myAttendance->getPostStatus()) || $myAttendance->getPostStatus() == "UNKNOWN" ? "not-set" : $eventTypes[$eventType]->getPostStatusSet()[$myAttendance->getPostStatus()]->getCode();

            if (!$canPlan)
                return $code == $myPostStatus && $myPostStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
            if (strtotime($startTime) > strtotime(date("c")))// pokud podminka plati, akce je budouci
                return $code == $myPreStatus ? "attendance$code active" : "attendance$code";
            else if ($myPostStatus == "not-set") // akce uz byla, post status nevyplnen
                return $code == $myPreStatus && $myPreStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
            else
                return $code == $myPostStatus && $myPostStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
        });

        $this->template->addFilter("statusColor", function (Status $status) {
            return $this->supplier->getStatusColor($status->getCode());
        });

        $this->template->statusList = $this->statusManager->getList();
    }

    /**
     * Transform array of events into event feed - array in format specified by FullCalendar specifications
     * 
     * @param Event[] $events
     * @return array
     */
    protected function toFeed(array $events): array
    {
        $feed = [];

        foreach ($events as $event) {
            /* @var $event Event */
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

    public function handleAttendance($id, $code, $desc)
    {
        $this->attendanceManager->create([
            "userId" => $this->user->getId(),
            "eventId" => $id,
            "preStatus" => $code,
            "preDescription" => $desc
        ]);

        if ($this->isAjax()) {
            $this->redrawControl("attendanceWarning");
            $this->redrawControl("attendanceTabs");
            $this->redrawNavbar();
        }
    }
}
