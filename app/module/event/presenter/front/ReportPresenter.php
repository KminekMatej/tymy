<?php

namespace Tymy\Module\Event\Presenter\Front;

use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

class ReportPresenter extends EventBasePresenter
{
    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public UserManager $userManager;

    public function renderDefault(?int $year = null, ?int $page = null)
    {
        $year = $year ?: date("Y");

        $this->addBreadcrumb($this->translator->translate("event.attendanceView"), $this->link(":Event:Report:", [$year, $page]));

        $yearEvents = $this->eventManager->getYearEvents($this->user->getId(), $year, $page);

        $years = range($yearEvents["firstYear"], $yearEvents["lastYear"]);

        $this->template->events = $yearEvents["events"];
        $this->template->year = $year;
        $this->template->currentPage = $yearEvents["page"];
        $this->template->lastPage = $yearEvents["lastPage"];
        $this->template->pagination = $this->pagination($yearEvents["totalCount"], EventManager::EVENTS_PER_PAGE, $yearEvents["page"], 5);
        $this->template->years = $years;
        $this->template->firstYear = $yearEvents["firstYear"];
        $this->template->lastYear = $yearEvents["lastYear"];
        $this->template->users = $this->userManager->getByStatus(User::STATUS_PLAYER);
    }
}
