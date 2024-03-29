<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Tymy\Module\Core\Manager;

use Exception;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\Application;
use Nette\Http\Request;
use Nette\Http\Response;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\RootPresenter;
use Tymy\Module\User\Model\User;

/**
 * Description of Responder
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 02.08.2020
 */
class Responder
{
    public ?RootPresenter $presenter = null;
    public ?RootPresenter $presenterMock = null;
    private int $httpCode = Response::S403_FORBIDDEN;

    /** @var null|mixed|mixed[] */
    private $payload;

    public function __construct(private Application $application, public Translator $translator)
    {
    }

    private function init(int $httpCode = Response::S403_FORBIDDEN): void
    {
        $this->presenter = $this->application->getPresenter();
        $this->payload = null;

        if (!$this->presenter && $this->presenterMock) {//work around to allow autotests to inject presenter
            $this->presenter = $this->presenterMock;
        }

        $this->httpCode = $httpCode;
    }

    /**
     * Respond using TymyResponse. TymyResponse is being catched on BasePresenters and trated differently, if it is API presenter or App presenter
     *
     * @param int $code Specific tymy app response code
     * @param string $message Additional response message
     * @param string|null $sessionKey SessionKey - used only for auth responses
     * @throws TymyResponse
     */
    private function respond(int $code, string $message, string $sessionKey = null): never
    {
        throw new TymyResponse(
            $message,
            $this->httpCode,
            $code,
            $this->payload,
            $this->httpCode >= 200 && $this->httpCode <= 299,
            $sessionKey
        );
    }

    private function throw(string $message): never
    {
        $this->presenter->getHttpResponse()->setCode($this->httpCode);

        throw new Exception($message);
    }

    //***********  ACCEPTED RESPONSES:


    public function A200_OK($payload = null): never
    {
        $this->init(Response::S200_OK);
        if ($payload !== null) {
            $this->payload = $payload;
        }
        $this->respond(200, "A200");
    }

    public function A201_CREATED($payload = null): never
    {
        $this->init(Response::S201_CREATED);
        if ($payload) {
            $this->payload = $payload;
        }
        $this->respond(201, "A201");
    }

    public function A304_NOT_MODIFIED($payload = null): never
    {
        $this->init(Response::S304_NOT_MODIFIED);
        if ($payload) {
            $this->payload = $payload;
        }
        $this->respond(304, "A304");
    }

    public function A2001_LOGGED_IN($userData, string $tsid): never
    {
        $this->init(Response::S200_OK);
        $this->payload = $userData;
        $this->respond(200, "A200", $tsid);
    }
    //***********  ERROR RESPONSES:

    /** @throws AbortException */
    public function E400_BAD_REQUEST($message = null): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(400, ($message ?: ""));
    }

    /** @throws AbortException */
    public function E401_UNAUTHORIZED($message = null): never
    {
        $this->init(Response::S401_UNAUTHORIZED);
        $this->respond(401, ($message ?: "Unauthorized"));
    }

    /** @throws AbortException */
    public function E403_FORBIDDEN($message = null): never
    {
        $this->init();
        $this->respond(403, ($message ?: "Forbidden"));
    }

    /** @throws AbortException */
    public function E404_NOT_FOUND(?string $module = null, $identifier = null): never
    {
        $this->init(Response::S404_NOT_FOUND);
        $message = match ($module) {
            User::MODULE => $this->translator->translate("common.alerts.userNotFound"),
            default => "Not-found",
        };
        $this->respond(404, $message, "Not-found");
    }

    /** @throws AbortException */
    public function E405_METHOD_NOT_ALLOWED(): never
    {
        $this->init(Response::S405_METHOD_NOT_ALLOWED);
        $this->respond(405, "E405", "Method not allowed");
    }

    /** @throws AbortException */
    public function E500_INTERNAL_SERVER_ERROR($throw = false): never
    {
        $this->init(Response::S500_INTERNAL_SERVER_ERROR);
        $throw ? $this->throw("E500") : $this->respond(500, "E500");
    }

    /** @throws AbortException */
    public function E4001_VIEW_NOT_PERMITTED($module, $id): never
    {
        $this->init();
        $this->respond(4001, "Forbidden to view `$id@$module`");
    }

    /** @throws AbortException */
    public function E4002_EDIT_NOT_PERMITTED($module, $id): never
    {
        $this->init();
        $this->respond(4002, "Forbidden to edit `$id@$module`");
    }

    /** @throws AbortException */
    public function E4003_CREATE_NOT_PERMITTED($module): never
    {
        $this->init();
        $this->respond(4003, "Forbidden to create record in `$module`");
    }

    /** @throws AbortException */
    public function E4004_DELETE_NOT_PERMITTED($module, $id): never
    {
        $this->init();
        $this->respond(4004, "Forbidden to delete `$id@$module`");
    }

    /** @throws AbortException */
    public function E4005_OBJECT_NOT_FOUND($module, $id): never
    {
        $this->init(Response::S404_NOT_FOUND);
        $this->respond(4005, "Object `$id@$module` not found");
    }

    /** @throws AbortException */
    public function E4006_INVALID_REQUEST_DATA(): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4006, "Invalid request data");
    }

    /** @throws AbortException */
    public function E4007_RELATION_PROHIBITS($field): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4007, "Relation prohibits this operation on field `$field`");
    }

    /** @throws AbortException */
    public function E4008_CHILD_NOT_RELATED_TO_PARENT($childModule, $childId, $parentModule, $parentId): never
    {
        $this->init();
        $this->respond(4008, "Child `$childId@$childModule` not related to `$parentId@$parentModule`");
    }

    /** @throws AbortException */
    public function E4009_CREATE_FAILED($module): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4009, "Creating record in module `$module` failed");
    }

    /** @throws AbortException */
    public function E4010_UPDATE_FAILED($module, $id): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4010, "Updating `$id@$module` failed");
    }

    /** @throws AbortException */
    public function E4011_DELETE_FAILED($module, $id): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4011, "Deleting `$id@$module` failed");
    }

    /** @throws AbortException */
    public function E4012_IMAGE_UPDATE_FAILED($module, $id): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4012, "Updating image at `$id@$module` failed");
    }

    /** @throws AbortException */
    public function E4013_MISSING_INPUT($inputName): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4013, "Missing input `$inputName`");
    }

    /** @throws AbortException */
    public function E4014_EMPTY_INPUT($inputName): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4014, "Empty input `$inputName`");
    }

    /** @throws AbortException */
    public function E4015_MISSING_URL_INPUT($inputName): never
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4015, "Missing url input `$inputName`");
    }

    /**
     * @param string $blockingModule
     * @param int[] $blockingIds
     * @throws AbortException
     */
    public function E4016_DELETE_BLOCKED_BY(string $blockingModule, array $blockingIds): never
    {
        $this->init(Response::S403_FORBIDDEN);
        $this->respond(4016, "Delete blocked by `$blockingModule` `" . implode(", ", $blockingIds) . "`");
    }

    /**
     * @param string $blockingModule
     * @param int[] $blockingIds
     * @throws AbortException
     */
    public function E4017_UPDATE_BLOCKED_BY(string $blockingModule, array $blockingIds): never
    {
        $this->init();
        $this->respond(4017, "Update blocked by `$blockingModule` `" . implode(", ", $blockingIds) . "`");
    }

    public function E4018_MIGRATION_FAILED(array $log = []): never
    {
        $this->init(500);
        $this->payload = $log;
        $this->respond(4018, "Migration failed");
    }
}
