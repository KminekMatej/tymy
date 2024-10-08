<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\DI\Attributes\Inject;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\User\Model\User;

class DetailPresenter extends EventBasePresenter
{
    #[Inject]
    public HistoryManager $historyManager;

    public function renderDefault(string $resource): void
    {
        $this->template->cptNotDecidedYet = $this->translator->translate('event.notDecidedYet');

        $eventId = $this->parseIdFromWebname($resource);
        $event = $this->eventManager->getById($eventId);
        assert($event instanceof Event);

        if (!$event instanceof Event) {
            $this->flashMessage($this->translator->translate("event.errors.eventNotExists", null, ['id' => $eventId]), "danger");
            $this->redirect(':Event:Default:');
        }

        $eventTypes = $this->eventTypeManager->getIndexedList();

        $this->addBreadcrumb($event->getCaption(), $this->link(":Event:Detail:", $event->getId() . "-" . $event->getWebName()));

        //results are closed if there is some attendance filled, in the UI its toggled by javascript
        $this->template->resultsClosed = !empty(array_filter(ArrayHelper::entityFields("postStatusId", $event->getAttendance()))) || !$event->getCanResult() || !$event->getInPast();

        $this->template->attendances = $this->loadEventAttendance($event);
        $this->template->event = $event;
        $this->template->eventTypes = $eventTypes;
        $this->template->eventType = $eventTypes[$event->getType()];
        $eventCaptions = $this->getEventCaptions($event, $eventTypes);
        $this->template->myPreStatusCaption = $eventCaptions["myPreStatusCaption"];
        $this->template->myPostStatusCaption = $eventCaptions["myPostStatusCaption"];
    }

    /**
     * Compose attendance array to be easily used on template
     *
     * @return array<int|string, array<int|string, mixed[]>>
     */
    private function loadEventAttendance(Event $event): array
    {
        $attendances = [];
        //return array, first key is attendance type (PRE, POST), then code (YES/NO/LAT), then gender (male/female/unknown), then user
        $usersWithAttendance = [];

        //prepare arrazs for each status to make the arraz properlz ordered
        $statusIds = $this->statusManager->getStatusIdsOfEventType($event->getEventTypeId());
        foreach ($statusIds as $statusId) {
            $attendances[$statusId] = [];
        }

        foreach ($event->getAttendance() as $attendance) {
            assert($attendance instanceof Attendance);
            $statusId = $attendance->getPostStatusId() ?: $attendance->getPreStatusId();
            $gender = $attendance->getUser()->getGender();

            if ($statusId == null || !array_key_exists($statusId, $attendances)) {
                //skip players when status id is empty (not-decided)
                continue;
            }

            if (!array_key_exists($gender, $attendances[$statusId])) {//init gender
                $attendances[$statusId][$gender] = [];
            }

            $attendances[$statusId][$gender][] = $attendance->getUser();
            $usersWithAttendance[] = $attendance->getUser()->getId();
        }

        //add all other players as not decided yet
        $users = $this->userManager->getByStatus(User::STATUS_PLAYER);
        foreach ($users as $user) {
            if (in_array($user->getId(), $usersWithAttendance)) {
                continue;   //user has already filled its attendance
            }
            $attendances["unknown"][$user->getGender()][] = $user;
        }
        return array_filter($attendances);  //return only attendances having at least one item
    }

    /**
     * @return array<string, mixed>
     */
    private function getEventCaptions(Event $event, array $eventTypes): array
    {
        return [
            "myPreStatusCaption" => empty($event->getMyAttendance()) || empty($event->getMyAttendance()->getPreStatus()) || $event->getMyAttendance()->getPreStatus() == "UNKNOWN" ? "not-set" : $eventTypes[$event->getType()]->getPreStatusSet()[$event->getMyAttendance()->getPreStatus()]->getCode(),
            "myPostStatusCaption" => empty($event->getMyAttendance()) || empty($event->getMyAttendance()->getPostStatus()) || $event->getMyAttendance()->getPostStatus() == "UNKNOWN" ? "not-set" : $eventTypes[$event->getType()]->getPostStatusSet()[$event->getMyAttendance()->getPostStatus()]->getCode(),
        ];
    }

    public function handleLoadHistory($udalost): void
    {
        $eventId = $this->parseIdFromWebname($udalost);
        $this->loadEventHistory($eventId);
        $this->redrawControl("history");
        $this->redrawControl("historyBtn");
    }

    public function handleAttendanceResult($id): void
    {
        $results = $this->getRequest()->getPost()["resultSet"];

        foreach ($results as $postStatusData) {
            $postStatusData["eventId"] = $id;
            $this->attendanceManager->create($postStatusData);
        }
        if ($this->isAjax()) {
            $this->redrawControl("attendanceTabs");
            $this->redrawNavbar();
        }
    }

    private function loadEventHistory(int $eventId): void
    {
        $this->template->histories = $this->historyManager->getEventHistory($eventId);
        $this->template->emptyStatus = (new Status())->setCode("")->setCaption($this->translator->translate('team.unknownSex'));
    }
}
