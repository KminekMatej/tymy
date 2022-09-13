<?php

namespace Tymy\Module\User\Presenter\Front;

use DateTimeZone;
use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Settings\Manager\ICalManager;

/**
 * Description of DetailPresenter
 *
 * @author kminekmatej, 11. 9. 2022, 21:35:32
 */
class DetailPresenter extends BasePresenter
{
    /** @inject */
    public ICalManager $iCalManager;
    
    /** @inject */
    public EventManager $eventManager;
    
    /** @inject */
    public StatusManager $statusManager;

    /** @inject */
    public Responder $responder;
    
    private array $statusNameCache = [];

    public function beforeRender()
    {
        parent::beforeRender();
        
        $this->template->addFilter("statusName", function (int $statusId) {
            if (!array_key_exists($statusId, $this->statusNameCache)) {
                /* @var $status Status */
                $status = $this->statusManager->getById($statusId);
                $this->statusNameCache[$statusId] = $status ? $status->getCaption() : "?";
            }

            return $this->statusNameCache[$statusId];
        });
    }

    public function renderCalendar(int $resource, string $hash)
    {
        $iCal = $this->iCalManager->getByUserId($resource);

        if (empty($iCal) || $iCal->getHash() !== $hash) {
            $this->responder->E404_NOT_FOUND("Calendar");
        }

        $this->template->iCal = $iCal;
        $this->template->dtz = new DateTimeZone("UTC");
        $this->template->serverName = $_SERVER["SERVER_NAME"];
        $this->template->now = (new DateTime())->setTimezone($this->template->dtz);
        $this->template->events = $this->eventManager->getEventsOfPrestatus($resource, $iCal->getStatusIds(), new DateTime("-90 days"));

        $this->getHttpResponse()->setContentType('text/calendar', 'UTF-8');
    }
}
