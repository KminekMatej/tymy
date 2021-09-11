<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Application\Responses\JsonResponse;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Model\Event;

class FeedPresenter extends SecuredPresenter
{

    /** @inject */
    public EventManager $eventManager;

    public function actionFeed(string $start, string $end)
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime($start), new DateTime($end));

        $this->sendResponse(new JsonResponse($this->toFeed($events)));
    }

    /**
     * Transform array of events into event feed - array in format specified by FullCalendar specifications
     * 
     * @param Event[] $events
     * @return array
     */
    private function toFeed(array $events): array
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
                "url" => $this->link("Event:event", $event->getWebName()),
            ];
        }

        return $feed;
    }

}