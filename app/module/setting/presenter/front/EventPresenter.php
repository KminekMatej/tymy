<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Utils\DateTime;
use stdClass;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Factory\FormFactory;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class EventPresenter extends SettingBasePresenter
{
    #[\Nette\DI\Attributes\Inject]
    public EventManager $eventManager;

    #[\Nette\DI\Attributes\Inject]
    public FormFactory $formFactory;

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("event.event", 2), $this->link(":Setting:Event:"));
    }

    public function actionDefault(?string $resource = null, int $page = 1): void
    {
        if ($resource) {
            $this->setView("event");
        }
    }

    public function renderDefault(?string $resource = null, int $page = 1): void
    {
        $limit = Event::PAGING_EVENTS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $this->template->events = $this->eventManager->getList(null, "id", $limit, $offset, "start_time DESC"); // get all events
        $allEventsCount = $this->eventManager->countAllEvents();
        $this->template->eventsCount = $allEventsCount;
        $this->template->currentPage = $page;
        $this->template->lastPage = ceil($allEventsCount / $limit);
        $this->template->pagination = $this->pagination($allEventsCount, $limit, $page, 5);
    }

    public function renderNew(): void
    {
        $this->allowPermission('EVE_CREATE');

        $this->addBreadcrumb($this->translator->translate("event.new", 2));
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

    public function renderEvent(string $resource): void
    {
        $this->allowPermission('EVE_UPDATE');

        //RENDERING EVENT DETAIL
        $eventId = $this->parseIdFromWebname($resource);
        $eventObj = $this->eventManager->getById($eventId);
        assert($eventObj instanceof Event);
        if ($eventObj == null) {
            $this->flashMessage($this->translator->translate("event.errors.eventNotExists", null, ['id' => $eventId]), "danger");
            $this->redirect(':Setting:Event:');
        }

        $this->addBreadcrumb($eventObj->getCaption(), $this->link(":Setting:Event:", $eventObj->getWebName()));
        $this->template->event = $eventObj;
    }

    public function handleEventDelete(int $eventId): void
    {
        try {
            $this->eventManager->delete($eventId);
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }
        $this->redirect(':Setting:Event:');
    }

    public function createComponentNewEventForm(): Form
    {
        return $this->formFactory->createEventLineForm(
            $this->eventTypes,
            fn(Form $form, \stdClass $values) => $this->newEventFormSuccess($form, $values)
        );
    }

    public function createComponentEventForm(): Multiplier
    {
        return new Multiplier(function (string $eventId): Form {
                $event = $this->eventManager->getById((int) $eventId);

                return $this->formFactory->createEventLineForm(
                    $this->eventTypes,
                    fn(Form $form, \stdClass $values) => $this->eventFormSuccess($form, $values),
                    $event
                );
        });
    }

    public function eventFormSuccess(Form $form, stdClass $values): void
    {
        try {
            $this->eventManager->update((array) $values, $values->id);
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        if (!$this->isAjax()) {
            $this->redirect("this");
        }
    }

    public function newEventFormSuccess(Form $form, stdClass $values): void
    {
        try {
            //load inputs until there are any more rows
            $baseKey = array_key_first((array) $values);
            $data = $form->getHttpData();

            $i = 0;
            $items = [];

            while (true) {
                $nextKey = $i > 0 ? $baseKey . "-" . $i : $baseKey;

                if (!array_key_exists($nextKey, $data)) {
                    break;
                }
                //this row exists
                $nextItem = [];
                foreach (array_keys((array) $values) as $name) {
                    $nextName = $i > 0 ? $name . "-" . $i : $name;
                    $nextItem[$name] = $data[$nextName];
                }
                $items[] = $nextItem;
                $i++;
            }

            $createdEvents = [];
            foreach ($items as $item) {
                \Tracy\Debugger::barDump($item);
                $createdEvents[] = $this->eventManager->create($item);
            }
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(':Setting:Event:');
    }
}
