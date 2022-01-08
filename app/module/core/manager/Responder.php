<?php

namespace Tymy\Module\Core\Manager;

use Exception;
use Nette\Application\AbortException;
use Nette\Application\Application;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Request;
use Nette\Http\Response;
use Tymy\Module\Core\Exception\ResponderException;
use Tymy\Module\Core\Presenter\Api\BasePresenter;

/**
 * Description of Responder
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 02.08.2020
 */
class Responder
{
    /** @var Application */
    private $application;

    /** @var BasePresenter */
    public $presenter;

    /** @var BasePresenter */
    public $presenterMock;

    /** @var int */
    private $httpCode = Response::S403_FORBIDDEN;

    /** @var mixed */
    private $payload;

    private Request $request;

    public function __construct(Application $application, Request $request)
    {
        $this->application = $application;
        $this->request = $request;
    }

    private function init($httpCode = Response::S403_FORBIDDEN)
    {
        $this->presenter = $this->application->getPresenter();
        $this->payload = null;

        if (!$this->presenter && $this->presenterMock) {//work around to allow autotests to inject presenter
            $this->presenter = $this->presenterMock;
        }

        $this->httpCode = $httpCode;
    }

    private function respond(int $code, string $message, string $sessionKey = null)
    {
        $this->presenter->getHttpResponse()->setCode($this->httpCode);
        $success = $this->httpCode >= 200 && $this->httpCode <= 299;

        $respond = [
            "status" => $success ? "OK" : "ERROR",
        ];

        if (!$success && !empty($message)) {
            $respond["statusMessage"] = $message;
        }

        if ($success && !empty($sessionKey)) {
            $respond["sessionKey"] = $sessionKey;
        }

        if ($this->payload !== null) {
            $respond["data"] = $this->payload;
        }

        if ($this->request->getQuery("debug") !== null) {//if this is some error response, add also message to generic payload object
            \Tracy\Debugger::barDump($respond, "Response");
            throw new ResponderException($message, $code);
        }

        $this->presenter->sendResponse(new JsonResponse(
            $respond,
            "application/json;charset=utf-8"
        ));
    }

    private function throw(string $message)
    {
        $this->presenter->getHttpResponse()->setCode($this->httpCode);

        throw new Exception($message);
    }

    //***********  ACCEPTED RESPONSES:


    public function A200_OK($payload = null)
    {
        $this->init(Response::S200_OK);
        if ($payload !== null) {
            $this->payload = $payload;
        }
        $this->respond(200, "A200");
    }

    public function A201_CREATED($payload = null)
    {
        $this->init(Response::S201_CREATED);
        if ($payload) {
            $this->payload = $payload;
        }
        $this->respond(201, "A201");
    }

    public function A304_NOT_MODIFIED($payload = null)
    {
        $this->init(Response::S304_NOT_MODIFIED);
        if ($payload) {
            $this->payload = $payload;
        }
        $this->respond(304, "A304");
    }

    public function A2001_LOGGED_IN($userData, $tsid)
    {
        $this->init(Response::S200_OK);
        $this->payload = $userData;
        $this->respond(200, "A200", $tsid);
    }
    //***********  ERROR RESPONSES:

    /** @throws AbortException */
    public function E400_BAD_REQUEST($message = null)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(400, ($message ? $message : ""));
    }

    /** @throws AbortException */
    public function E401_UNAUTHORIZED($message = null)
    {
        $this->init(Response::S401_UNAUTHORIZED);
        $this->respond(401, ($message ? $message : "Unauthorized"));
    }

    /** @throws AbortException */
    public function E403_FORBIDDEN($message = null)
    {
        $this->init();
        $this->respond(403, ($message ? $message : "Forbidden"));
    }

    /** @throws AbortException */
    public function E404_NOT_FOUND()
    {
        $this->init(Response::S404_NOT_FOUND);
        $this->respond(404, "E404", "Not-found");
    }

    /** @throws AbortException */
    public function E405_METHOD_NOT_ALLOWED()
    {
        $this->init(Response::S405_METHOD_NOT_ALLOWED);
        $this->respond(405, "E405", "Method not allowed");
    }

    /** @throws AbortException */
    public function E500_INTERNAL_SERVER_ERROR($throw = false)
    {
        $this->init(Response::S500_INTERNAL_SERVER_ERROR);
        $throw ? $this->throw("E500") : $this->respond(500, "E500");
    }

    /** @throws AbortException */
    public function E4001_VIEW_NOT_PERMITTED($module, $id)
    {
        $this->init();
        $this->respond(4001, "Forbidden to view `$id@$module`");
    }

    /** @throws AbortException */
    public function E4002_EDIT_NOT_PERMITTED($module, $id)
    {
        $this->init();
        $this->respond(4002, "Forbidden to edit `$id@$module`");
    }

    /** @throws AbortException */
    public function E4003_CREATE_NOT_PERMITTED($module)
    {
        $this->init();
        $this->respond(4003, "Forbidden to create record in `$module`");
    }

    /** @throws AbortException */
    public function E4004_DELETE_NOT_PERMITTED($module, $id)
    {
        $this->init();
        $this->respond(4004, "Forbidden to delete `$id@$module`");
    }

    /** @throws AbortException */
    public function E4005_OBJECT_NOT_FOUND($module, $id)
    {
        $this->init(Response::S404_NOT_FOUND);
        $this->respond(4005, "Object `$id@$module` not found");
    }

    /** @throws AbortException */
    public function E4006_INVALID_REQUEST_DATA()
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4006, "Invalid request data");
    }

    /** @throws AbortException */
    public function E4007_RELATION_PROHIBITS($field)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4007, "Relation prohibits this operation on field `$field`");
    }

    /** @throws AbortException */
    public function E4008_CHILD_NOT_RELATED_TO_PARENT($childModule, $childId, $parentModule, $parentId)
    {
        $this->init();
        $this->respond(4008, "Child `$childId@$childModule` not related to `$parentId@$parentModule`");
    }

    /** @throws AbortException */
    public function E4009_CREATE_FAILED($module)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4009, "Creating record in module `$module` failed");
    }

    /** @throws AbortException */
    public function E4010_UPDATE_FAILED($module, $id)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4010, "Updating `$id@$module` failed");
    }

    /** @throws AbortException */
    public function E4011_DELETE_FAILED($module, $id)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4011, "Deleting `$id@$module` failed");
    }

    /** @throws AbortException */
    public function E4012_IMAGE_UPDATE_FAILED($module, $id)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4012, "Updating image at `$id@$module` failed");
    }

    /** @throws AbortException */
    public function E4013_MISSING_INPUT($inputName)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4013, "Missing input `$inputName`");
    }

    /** @throws AbortException */
    public function E4014_EMPTY_INPUT($inputName)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4014, "Empty input `$inputName`");
    }

    /** @throws AbortException */
    public function E4015_MISSING_URL_INPUT($inputName)
    {
        $this->init(Response::S400_BAD_REQUEST);
        $this->respond(4015, "Missing url input `$inputName`");
    }

    /**
     * @param string $module
     * @param int $recordId
     * @param string $blockingModule
     * @param int[] $blockingIds
     * @throws AbortException
     */
    public function E4016_DELETE_BLOCKED_BY(string $blockingModule, array $blockingIds)
    {
        $this->init(Response::S403_FORBIDDEN);
        $this->respond(4016, "Delete blocked by `$blockingModule` `" . join(", ", $blockingIds) . "`");
    }

    /**
     * @param string $module
     * @param int $recordId
     * @param string $blockingModule
     * @param int[] $blockingIds
     * @throws AbortException
     */
    public function E4017_UPDATE_BLOCKED_BY(string $blockingModule, array $blockingIds)
    {
        $this->init();
        $this->respond(4017, "Update blocked by `$blockingModule` `" . join(", ", $blockingIds) . "`");
    }
}
