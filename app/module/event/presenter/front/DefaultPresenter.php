<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Core\Model\BaseModel;

class DefaultPresenter extends EventBasePresenter
{
    /** @inject */
    public HistoryManager $historyManager;


    public function renderDefault($date = null, $direction = null): void
    {
        $dateTimeBase = new DateTime();
        $dateTimeFrom = $dateTimeBase->modifyClone("- 6 months")->setTime(0, 0, 0);
        $dateTimeUntil = $dateTimeBase->modifyClone("+ 6 months")->setTime(23, 59, 59);

        if ($direction == 1) {
            $dateTimeUntil = (new DateTime($date))->modify("+ 6 months");
        } elseif ($direction == -1) {
            $dateTimeFrom = (new DateTime($date))->modify("- 6 months");
        }

        $events = $this->eventManager->getEventsInterval($this->user->getId(), $dateTimeFrom, $dateTimeUntil, "startTime__asc");

        $this->template->agendaFrom = $dateTimeFrom->format(BaseModel::YEAR_MONTH);
        $this->template->agendaTo = $dateTimeUntil->format(BaseModel::YEAR_MONTH);
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->eventTypes = $this->eventTypeManager->getIndexedList();
        $this->template->events = $events;

        $this->template->evMonths = $this->eventManager->getAsMonthArray($events);

        if ($this->isAjax()) {
            $this->payload->events = $this->toFeed($events);
        }
    }

    public function handleEventLoad(): void
    {
        $this->redrawControl("events-agenda");
    }
}
