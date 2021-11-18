<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Utils\DateTime;
use Tapi\EventListResource;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class EventPresenter extends SettingBasePresenter
{

    /** @inject */
    public EventManager $eventManager;

    public function actionDefault(?string $resource = null, int $page = 1)
    {
        if ($resource) {
            $this->setView("event");
        }
    }

    public function renderDefault(?string $resource = null, int $page = 1)
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("event.event", 2), "link" => $this->link(":Setting:Event:")]]);
        $limit = EventListResource::PAGING_EVENTS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $this->template->events = $this->eventManager->getList(null, "id", $limit, $offset); // get all events
        $allEventsCount = $this->eventManager->countAllEvents();
        $this->template->eventsCount = $allEventsCount;
        $this->template->currentPage = $page;
        $this->template->lastPage = ceil($allEventsCount / $limit);
        $this->template->pagination = $this->pagination($allEventsCount, $limit, $page, 5);
    }

    public function renderNew()
    {
        $this->allowPermission('EVE_CREATE');

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("event.event", 2), "link" => $this->link(":Setting:Event:")],
            "3" => ["caption" => $this->translator->translate("event.new", 2)]
        ]);
        $this->template->events = [
                    (new Event())
                    ->setId(-1)
                    ->setCaption("")
                    ->setDescription("")
                    ->setStartTime(new DateTime("+ 24 hours"))
                    ->setEndTime(new DateTime("+ 25 hours"))
                    ->setCloseTime(new DateTime("+ 23 hours"))
                    ->setPlace("")
                    ->setLink("")
        ];
    }

    public function renderEvent(string $resource)
    {
        $this->allowPermission('EVE_UPDATE');

        //RENDERING EVENT DETAIL
        $eventId = $this->parseIdFromWebname($resource);
        /* @var $eventObj Event */
        $eventObj = $this->eventManager->getById($eventId);
        if ($eventObj == NULL) {
            $this->flashMessage($this->translator->translate("event.errors.eventNotExists", NULL, ['id' => $eventId]), "danger");
            $this->redirect(':Setting:Event:');
        }

        $this->setLevelCaptions(["3" => ["caption" => $eventObj->getCaption(), "link" => $this->link(":Setting:Event:", $eventObj->getWebName())]]);
        $this->template->event = $eventObj;
    }

    public function handleEventsEdit()
    {
        $this->allowPermission('EVE_UPDATE');

        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->editEvent($bind);
        }
    }

    public function handleEventsCreate()
    {
        $this->allowPermission('EVE_CREATE');

        $binders = $this->getRequest()->getPost()["binders"];
        
        foreach ($binders as &$bind) {
            $this->normalizeDates($bind["changes"]);
            $this->eventManager->create($bind["changes"]);
        }

        $this->redirect(':Setting:Event:');
    }
    
    private function normalizeDates(array &$data)
    {
        foreach (["startTime", "endTime", "closeTime"] as $timeKey) {
            if (isset($data[$timeKey])) {
                $data[$timeKey] = new DateTime($data[$timeKey]);
            }
        }
    }

    public function handleEventEdit()
    {
        $this->editEvent($this->getRequest()->getPost());
    }

    public function handleEventDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->eventManager->delete($bind["id"]);
    }

    private function editEvent($bind)
    {
        if (array_key_exists("startTime", $bind["changes"])) {
            $bind["changes"]["startTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["startTime"]));
        }

        if (array_key_exists("endTime", $bind["changes"])) {
            $bind["changes"]["endTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["endTime"]));
        }

        if (array_key_exists("closeTime", $bind["changes"])) {
            $bind["changes"]["closeTime"] = gmdate("Y-m-d\TH:i:s\Z", strtotime($bind["changes"]["closeTime"]));
        }

        $this->eventManager->update($bind["changes"], $bind["id"]);
    }

}