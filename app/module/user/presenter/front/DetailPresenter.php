<?php

namespace Tymy\Module\User\Presenter\Front;

use DateTimeZone;
use Nette\Application\Responses\CallbackResponse;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\IRequest;
use Nette\Http\Response;
use Nette\Utils\Arrays;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Settings\Manager\ICalManager;

use function mb_str_split;

/**
 * Description of DetailPresenter
 */
class DetailPresenter extends BasePresenter
{
    #[\Nette\DI\Attributes\Inject]
    public ICalManager $iCalManager;

    #[\Nette\DI\Attributes\Inject]
    public EventManager $eventManager;

    #[\Nette\DI\Attributes\Inject]
    public StatusManager $statusManager;

    #[\Nette\DI\Attributes\Inject]
    public Responder $responder;

    private array $statusNameCache = [];

    public function beforeRender(): void
    {
        parent::beforeRender();

        assert($this->template instanceof Template);
        $this->template->addFilter("statusName", function (int $statusId) {
            if (!array_key_exists($statusId, $this->statusNameCache)) {
                $status = $this->statusManager->getById($statusId);
                assert($status instanceof Status);
                $this->statusNameCache[$statusId] = $status !== null ? $status->getCaption() : "?";
            }

            return $this->statusNameCache[$statusId];
        });

        $this->template->addFilter("splitDescription", function (?string $description = null): ?string {
            if (empty($description)) {
                return $description;
            }

            $separatedParts = mb_str_split(str_replace(["\r\n", "\n", ";", ","], ["\\n", "\\n", "\;", "\,"], $description), 62);
            return Strings::normalize(implode(PHP_EOL . " ", $separatedParts));
        });
    }

    public function renderCalendar(int $resource, string $hash): void
    {
        $iCal = $this->iCalManager->getByUserId($resource);

        if (empty($iCal) || $iCal->getHash() !== $hash) {
            $this->getHttpResponse()->setCode(404);
            $this->sendJson("Calendar not found");
        }

        $this->template->iCal = $iCal;
        $this->template->dtz = new DateTimeZone("UTC");
        $this->template->serverName = $_SERVER["SERVER_NAME"];
        $this->template->now = (new DateTime())->setTimezone($this->template->dtz);
        $this->template->events = $this->eventManager->getEventsOfPrestatus($resource, $iCal->getStatusIds(), new DateTime("-90 days"));

        $this->sendAsIcal($this->template);
    }

    /**
     * Send response in iCal formatting, with respect to some ical specific formatting
     *
     * @return never
     */
    private function sendAsIcal(Template $template): void
    {
        $files = $this->formatTemplateFiles();
        foreach ($files as $file) {
            if (is_file($file)) {
                $template->setFile($file);
                break;
            }
        }

        if (!$template->getFile()) {
            $file = strtr(Arrays::first($files), '/', DIRECTORY_SEPARATOR);
            $this->error("Page not found. Missing template '$file'.");
        }

        $this->sendResponse(
            new CallbackResponse(function (IRequest $request, Response $response) use ($template): void {
                    $response->setContentType('text/calendar');

                    echo str_replace(PHP_EOL, "\r\n", $template->renderToString());
            })
        );
    }
}
