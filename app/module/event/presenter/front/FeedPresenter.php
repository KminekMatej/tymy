<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Application\Responses\JsonResponse;
use Nette\Utils\DateTime;
use Tymy\Module\Event\Manager\EventManager;

class FeedPresenter extends EventBasePresenter
{
    #[\Nette\DI\Attributes\Inject]
    public EventManager $eventManager;

    public function actionDefault(string $start, string $end): void
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime($start), new DateTime($end));

        $this->sendResponse(new JsonResponse($this->toFeed($events)));
    }
}
