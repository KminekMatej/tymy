<?php

namespace Tymy\Module\Core\Presenter\Api;

use Nette\Application\AbortException;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\JsonResponse;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response as HttpResponse;
use Nette\Utils\JsonException;
use Tracy\Debugger;
use Tracy\ILogger;
use Tymy\Module\Core\Exception\DebugResponse;
use Tymy\Module\Core\Exception\DeleteIntegrityException;
use Tymy\Module\Core\Exception\IntegrityException;
use Tymy\Module\Core\Exception\MissingInputException;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Exception\UpdateIntegrityException;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\RootPresenter;

/**
 * Description of BasePresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class BasePresenter extends RootPresenter
{
    /** @inject */
    public Responder $responder;
    private Explorer $mainDatabase;
    protected ?ActiveRow $resourceRow = null;
    protected ?ActiveRow $subResourceRow = null;
    protected BaseManager $manager;
    /** @inject */
    public HttpRequest $httpRequest;
    /** @inject */
    public HttpResponse $httpResponse;

    /** @var mixed */
    protected $requestData;
    
    public function __construct(Container $container)
    {
        $this->mainDatabase = $container->getByName("database.main.context");
        parent::__construct();
    }

    protected function startup()
    {
        Debugger::timer("request");
        parent::startup();

        if (!$this->requestData && !empty($this->getHttpRequest()->getRawBody())) {
            $ctHeader = explode(";", $this->getHttpRequest()->getHeader("Content-Type"));
            if (!empty($ctHeader) && $ctHeader[0] == "application/x-www-form-urlencoded") {
                $this->decodeUrlEncodedData();
            } else {
                $this->decodeJsonData();
            }
        }
    }

    /**
     * Decode input request data passed as url-encoded string
     * 
     * @return void
     */
    private function decodeUrlEncodedData(): void
    {
        parse_str($this->getHttpRequest()->getRawBody(), $this->requestData);

        if (!empty($this->getHttpRequest()->getRawBody()) && empty($this->requestData)) {
            Debugger::log("Parsing url-encoded request failed: " . print_r($this->getHttpRequest()->getRawBody(), true), ILogger::ERROR);
            $this->responder->E4006_INVALID_REQUEST_DATA();
        }
    }

    /**
     * Decode input request data passed as json-encoded string
     * @return void
     * @throws JsonException
     */
    private function decodeJsonData(): void
    {
        try {
            $this->requestData = \json_decode($this->getHttpRequest()->getRawBody(), true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new JsonException();
            }
        } catch (JsonException $e) {
            Debugger::log($e->getMessage() . ": " . print_r($this->getHttpRequest()->getRawBody(), true), ILogger::ERROR);
            $this->responder->E4006_INVALID_REQUEST_DATA();
        }
    }

    protected function requestGet($resourceId, $subResourceId)
    {
        try {
            $record = $this->manager->read($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($record->jsonSerialize());
    }

    protected function requestPost($resourceId)
    {
        try {
            $created = $this->manager->create($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($created->jsonSerialize());
    }

    protected function requestPut($resourceId, $subResourceId)
    {
        try {
            $updated = $this->manager->update($this->requestData, $resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($updated->jsonSerialize());
    }

    protected function requestDelete($resourceId, $subResourceId)
    {
        try {
            $deletedId = $this->manager->delete($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondDeleted($deletedId);
    }

    /**
     * Simple exception handler. If any exception gets throws, logs message into exception.log file and then either responds proper response, or continue throwing the response
     *
     * @param \Exception $exc
     */
    protected function handleException(\Exception $exc)
    {
        if ($exc instanceof AbortException) {
            throw $exc; //when its aborted, simply continue with abortion
        }

        Debugger::log($exc->getMessage(), ILogger::EXCEPTION);

        if (is_a($exc, DeleteIntegrityException::class)) {
            /* @var $exc DeleteIntegrityException */
            $this->responder->E4016_DELETE_BLOCKED_BY($exc->fkTable, $exc->blockingIds);
        }

        if (is_a($exc, UpdateIntegrityException::class)) {
            /* @var $exc UpdateIntegrityException */
            $this->responder->E4017_UPDATE_BLOCKED_BY($exc->fkTable, $exc->blockingIds);
        }

        if (is_a($exc, IntegrityException::class)) {
            /* @var $exc IntegrityException */
            $this->responder->E4007_RELATION_PROHIBITS($exc->failingField);
        }

        if (is_a($exc, MissingInputException::class)) {
            /* @var $exc IntegrityException */
            $this->responder->E4013_MISSING_INPUT($exc->getMessage());
        }

        throw $exc;
    }

    protected function respondOk($payload = null)
    {
        $this->responder->A200_OK($payload);
    }

    protected function respondOkCreated($payload = null)
    {
        $this->responder->A201_CREATED($payload);
    }

    protected function respondDeleted($id)
    {
        $this->respondOk(["id" => (int) $id]);
    }

    protected function respondBadRequest($message = null)
    {
        $this->responder->E400_BAD_REQUEST($message);
    }

    protected function respondUnauthorized()
    {
        $this->responder->E401_UNAUTHORIZED();
    }

    protected function respondForbidden()
    {
        $this->responder->E403_FORBIDDEN("Nedostatečná práva");
    }

    protected function respondNotFound()
    {
        $this->responder->E404_NOT_FOUND();
    }

    protected function respondNotAllowed()
    {
        $this->responder->E405_METHOD_NOT_ALLOWED();
    }

    /**
     * If resourceId is supplied, load desired object using supplied manager.
     * Fills resourceRow property and returns BaseModel (resourceRow mapped using BaseManager)
     *
     * @param int $resourceId
     * @return BaseModel|false Model mapped
     */
    protected function loadResource($resourceId, BaseManager $manager)
    {
        if (!isset($resourceId)) {
            return null;
        }

        $this->resourceRow = $manager->getRow($resourceId);

        if (!$this->resourceRow) {
            $this->responder->E4005_OBJECT_NOT_FOUND($manager->getModule(), $resourceId);
        }

        return $manager->map($this->resourceRow);
    }

    /**
     * If subResourceId is supplied, load desired object using supplied manager.
     * Fills subResourceRow property and returns BaseModel (subResourceRow mapped using BaseManager)
     *
     * @param int $subResourceId
     * @return BaseModel|false Model mapped
     */
    protected function loadSubResource($subResourceId, BaseManager $manager)
    {
        if (!isset($subResourceId)) {
            return null;
        }

        $this->subResourceRow = $manager->getRow($subResourceId);

        if (!$this->subResourceRow) {
            $this->responder->E4005_OBJECT_NOT_FOUND($manager->getModule(), $subResourceId);
        }

        return $manager->map($this->subResourceRow);
    }

    /**
     * Transform array of entities into jsonizable array
     *
     * @param BaseModel[] $entities
     * @return array
     */
    protected function arrayToJson($entities)
    {
        if (empty($entities)) {
            return [];
        }

        return array_map(function ($entity) {
            /* @var $entity BaseModel */
            return $entity->jsonSerialize();
        }, $entities);
    }

    /**
     * Check that supplied array is arrray of objects - used to detect whether POST or PUT request input data are single or multiple objects
     * @param array $array
     * @return boolean
     */
    public function isMultipleObjects($array)
    {
        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $innArr) {
            if (!is_array($innArr)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Simple function to throw Bad Request if suplied parametr is non-truthy
     * @param mixed $parameter
     */
    protected function needs($parameter = null)
    {
        if (!$parameter) {
            $this->respondBadRequest();
        }
    }

    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;
    }
    
    public function terminate(): void
    {
        //log request & terminate
        $this->mainDatabase->table("api_log")->insert(
                [
                    "remote_host" => $this->httpRequest->getRemoteHost(),
                    "request_url" => $this->httpRequest->getUrl()->absoluteUrl,
                    "response_status" => $this->httpResponse->getCode(),
                    "time_in_ms" => round(Debugger::timer("request") * 1000),
                ]);
        
        parent::terminate();
    }
    
    /**
     * Allow access to this presenter only if debugger mode is enabled.
     * If debugger is disabled, redirect to Homepage
     */
    protected function allowOnlyInDebuggerMode(): void
    {
        if(Debugger::$productionMode){  //
            $this->redirect("Core:Default:Default");
        }
    }

    public function run(Request $request): Response
    {
        try {
            return parent::run($request);
        } catch (TymyResponse $tResp) {
            $this->getHttpResponse()->setCode($tResp->getHttpCode());

            $respond = [
                "status" => $tResp->getSuccess() ? "OK" : "ERROR",
            ];

            if (!$tResp->getSuccess() && !empty($tResp->getMessage())) {
                $respond["statusMessage"] = $tResp->getMessage();
            }

            if ($tResp->getSuccess() && !empty($tResp->getSessionKey())) {
                $respond["sessionKey"] = $tResp->getSessionKey();
            }

            if ($this->payload !== null) {
                $respond["data"] = $tResp->getPayload();
            }

            if ($this->httpRequest->getQuery("debug") !== null) {//if this is some error response, add also message to generic payload object
                Debugger::barDump([
                    $respond
                    ], "Response");
                throw new DebugResponse($message, $code);
            }

            return new JsonResponse(
                $respond,
                "application/json;charset=utf-8"
            );
        }
    }

}
