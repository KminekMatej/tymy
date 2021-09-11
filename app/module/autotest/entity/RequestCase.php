<?php

namespace Tymy\Module\Autotest;

use Tymy\Bootstrap;
use Nette\Application\BadRequestException;
use Nette\Application\IResponse;
use Nette\Application\PresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\Routers\RouteList;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Http\Request as Request2;
use Nette\Http\RequestFactory;
use Nette\InvalidStateException;
use Nette\Neon\Neon;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tester\Environment;
use Tester\TestCase;
use Tymy\Module\Authentication\Manager\AuthenticationManager;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Autotest\Entity\Assert;
use const ROOT_DIR;
use const TEST_DIR;

/**
 * Envelope class for all api testing classes
 *
 * @author kminekmatej, 10.3.2019
 */
abstract class RequestCase extends TestCase
{
    public const REGEX_JSON_DATE = '#^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}#';
    private const CHARS = '\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}';

    /** @var JsonResponse */
    protected $jsonResponse;

    /** @var Container */
    protected $container;

    /** @var User */
    protected $user;
    protected $curl;

    /** @var array */
    protected $config;

    /** @var array */
    protected $moduleConfig;

    /** @var RecordManager */
    protected $recordManager;

    /** @var RouteList */
    private $routeList;

    /** @var Request2 */
    private $httpRequest;

    /** @var RequestFactory */
    private $requestFactory;

    /** @var PresenterFactory */
    private $presenterFactory;

    /** @var AuthenticationManager */
    protected $authenticationManager;

    /** @var Responder */
    protected $responder;

    /** @var RequestLog[] */
    private array $logs = [];

    public function __construct(Container $container)
    {
        define('TEST_DIR', Bootstrap::normalizePath(Bootstrap::MODULES_DIR . "/autotest"));
        define('WWW_DIR', Bootstrap::normalizePath(ROOT_DIR . "/www"));
        $this->container = $container;
        $this->user = $this->container->getByType(User::class);
        $this->authenticationManager = $this->container->getByType(AuthenticationManager::class);
        $this->httpRequest = $this->container->getService("http.request");
        $this->requestFactory = $this->container->getService("http.requestFactory");
        $this->presenterFactory = $this->container->getService("application.presenterFactory");
        $this->responder = $this->container->getService("Responder");
        $this->routeList = $this->container->getService("router");
        $this->config = Neon::decode(file_get_contents(TEST_DIR . '/autotest.records.map.neon'));
        $this->moduleConfig = isset($this->config[$this->getModule()]) ? $this->config[$this->getModule()] : [];
        $this->recordManager = new RecordManager($this, $this->config);
        Environment::setup();
    }

    /**
     * Function should return string of module name, preferably from constant
     * @return string
     */
    abstract public function getModule();

    abstract public function getBasePath();

    abstract public function createRecord();

    public function deleteRecord($recordId)
    {
        return $this->recordManager->deleteRecord($this->getBasePath(), $recordId);
    }

    abstract public function mockRecord();

    abstract protected function mockChanges(): array;

    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    protected function tearDown()
    {
        //process request logs and save them to file
        if (empty($this->logs)) {
            return;
        }

        $fp = fopen(ROOT_DIR . '/log/requests.log', 'a+');
        flock($fp, LOCK_EX);

        foreach ($this->logs as $requestLog) {
            $coded = !empty($requestLog->getExpectCode());
            if ($requestLog->getExpectCode() > 999) {
                if ($requestLog->getCustomResponseCode() == $requestLog->getExpectCode()) {
                    $codeStr = ", code: {$requestLog->getExpectCode()}";
                    $success = true;
                } else {
                    $codeStr = ", code: {$requestLog->getCustomResponseCode()}/{$requestLog->getExpectCode()}";
                    $success = false;
                }
            } else {
                if ($requestLog->getHttpResponseCode() == $requestLog->getExpectCode()) {
                    $codeStr = ", code: {$requestLog->getExpectCode()}";
                    $success = true;
                } else {
                    $codeStr = ", code: {$requestLog->getHttpResponseCode()}/{$requestLog->getExpectCode()}";
                    $success = false;
                }
            }
            /* @var $requestLog RequestLog */
            $data = $requestLog->getPostData();
            $clrStart = "";
            $clrEnd = "";
            if ($coded) {
                if ($success) {
                    $clrStart = "[green]";
                    $clrEnd = "[/green]";
                } else {
                    $clrStart = "[red]";
                    $clrEnd = "[/red]";
                }
            }
            $string = ($requestLog->getTime())->format(BaseModel::DATETIME_CZECH_FORMAT) . "$clrStart {$requestLog->getMethod()}: {$requestLog->getUrl()}";
            if (!empty($data)) {
                $string .= ", data: " . json_encode($data);
            }
            if (!empty($coded)) {
                $string .= $codeStr;
            }

            $string .= "$clrEnd " . PHP_EOL;
            fwrite($fp, $string);
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        $this->logs = [];
    }

    protected function change(int $recordId, ?array $changes = null)
    {
        $changes = $changes ?: $this->mockChanges();

        $changedData = $this->request($this->getBasePath() . "/" . $recordId, "PUT", $changes)->expect(200);

        foreach ($changes as $key => $changed) {
            Assert::hasKey($key, $changedData->getData(), "Output key [$key] missing");
            Assert::equal($changed, $changedData->getData()[$key], "Output key [$key] mismatch (expected $changed, returned ".$changedData->getData()[$key].")");
        }
    }

    //*************** COMMON TESTS, SAME FOR ALL MODULES

    public function testUnauthorized()
    {
        $this->user->logout(true);

        $this->request($this->getBasePath())->expect(401);
        $this->request($this->getBasePath(), 'POST', $this->mockRecord())->expect(401);
        $this->request($this->getBasePath() . "/1", 'PUT', $this->mockRecord())->expect(401);
        $this->request($this->getBasePath() . "/1", 'DELETE')->expect(401);
    }

    public function testMethodNotAllowed()
    {
        $this->authorizeAdmin();

        $this->request($this->getBasePath(), 'HEAD')->expect(405);

        $this->user->logout(true);
    }

    //*************** END:COMMON TESTS

    /** @return SimpleResponse */
    public function request($url, $method = "GET", $data = [], $responseClass = null)
    {
        $url = $url[0] == "/" ? $url : "/" . $url;
        $httpRequest = $this->mockHttpRequest($method, $url, $data);

        $this->logs[] = $log = new RequestLog($method, $url, $data);

        $request = $this->createInitialRequest($httpRequest);

        Assert::type(Request::class, $request, "No route found for url $url");
        $presenterMock = $this->loadPresenter($request->getPresenterName());
        $presenterMock->setRequestData($data);
        $this->responder->presenterMock = $presenterMock;
        /* @var $response IResponse */
        $response = $presenterMock->run($request);
        $httpResponse = $presenterMock->getHttpResponse();
        if (!$responseClass) {
            Assert::type(JsonResponse::class, $response);
            return new SimpleResponse($httpResponse->getCode(), ($response->getPayload()["status"] == "OK" ? ($response->getPayload()["data"] ?? null) : null), $request, $httpResponse, $response, $presenterMock, $log);
        } elseif ($responseClass == TextResponse::class) {
            Assert::type($responseClass, $response);
            return new SimpleResponse($httpResponse->getCode(), $response->getSource(), $request, $httpResponse, $response, $presenterMock, $log);
        } else {
            Assert::type($responseClass, $response);
            return new SimpleResponse($httpResponse->getCode(), null, $request, $httpResponse, $response, $presenterMock, $log);
        }
    }

    private function createInitialRequest($httpRequest): Request
    {
        $params = $this->routeList->match($httpRequest);

        $presenter = $params[Presenter::PRESENTER_KEY] ?? null;

        if ($params === null) {
            throw new BadRequestException('No route for HTTP request.');
        } elseif (!is_string($presenter)) {
            throw new InvalidStateException('Missing presenter in route definition.');
        }

        unset($params[Presenter::PRESENTER_KEY]);
        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            [Request::SECURED => $httpRequest->isSecured()]
        );
    }

    /* @todo structure comparer - maybe not so needed */
    protected function compareStructure(array $expectedStructure, array $actualData)
    {
        /*
         * $expectedStructure = [
         *  "name" => "string(0,19)",
         *  "amount" => "int(0,35)",
         *  "canRead" => "bool",
         *  "subProperty" => [
         *      "subName" => "string"
         *      "subId" => "int"
         * ],
         * ]
         *
         */
        $re = '/(string|int|array|bool)(\((\d+),(\d+)\))?/m';
        foreach ($expectedStructure as $expectedKey => $property) {
            //check that this structure key is set also in data
            Assert::hasKey($expectedKey, $actualData);
            $matches = [];
            $matched = preg_match($re, $property, $matches);
            Assert::true($matched);
            $type = $matches[1];
            $min = $max = null;

            Assert::type($type, $actualData[$expectedKey]);

            if (count($matches) > 1) {
                $min = $matches[3];
                $max = $matches[4];
                switch ($type) {
                    case "string":
                        $length = strlen($actualData[$expectedKey]);
                        Assert::true($length <= $max);
                        Assert::true($length >= $min);
                        break;
                    case "int":
                        $length = strlen($actualData[$expectedKey]);
                        Assert::true($length <= $max);
                        Assert::true($length >= $min);
                        break;

                    default:
                        break;
                }
            }
        }
    }

    public function authorizeUser($userName = null, $password = null)
    {
        $this->user->logout(true);
        $this->user->setAuthenticator($this->authenticationManager);
        $this->user->login($userName ? $userName : $this->config["user_test_login"], $password ? $password : $this->config["user_test_pwd"]);

        Assert::true($this->user->isLoggedIn());
        Assert::equal($this->user->id, $this->config["user_test_id"]);
    }

    public function authorizeAdmin($userName = null, $password = null)
    {
        $this->user->logout(true);
        $this->user->setAuthenticator($this->authenticationManager);
        $this->user->login($userName ? $userName : $this->config["user_admin_login"], $password ? $password : $this->config["user_admin_pwd"]);

        Assert::true($this->user->isLoggedIn());
        if (empty($userName)) {
            Assert::equal($this->user->id, $this->config["user_admin_id"]);
        }
    }

    public function _testObjectEquality($original, $new, $skip = null)
    {
        foreach ($original as $key => $value) {
            if ($skip && ((is_array($skip) && in_array($key, $skip)) || $key == $skip)) {
                continue;
            }
            $newEnc = json_encode($new);
            Assert::true(array_key_exists($key, $new), "Field `$key` aint returned. Returned data: " . $newEnc);
            Assert::equal($value, $new[$key], "Error on `$key` field");
        }
    }

    /** @return Presenter */
    protected function loadPresenter($name)
    {
        $presenter = $this->presenterFactory->createPresenter($name);
        $presenter->autoCanonicalize = false;
        return $presenter;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Mock http request based on method and request url. Request url must always be relative, starting with /module (do not add /api here)
     * @param type $method
     * @param type $requestUrl
     * @return Request2
     */
    private function mockHttpRequest($method, $requestUrl, $data)
    {
        $requestMockFactory = new RequestMockFactory();

        $SERVER["HTTPS"] = "on";
        $SERVER["HTTP_HOST"] = getenv("SERVER_NAME") ?: "autotest.tymy.cz";
        $SERVER["SERVER_NAME"] = getenv("SERVER_NAME") ?: "autotest.tymy.cz";
        $SERVER["SERVER_PORT"] = "443";
        $SERVER["REQUEST_URI"] = "/api$requestUrl";
        $SERVER["SCRIPT_NAME"] = "/api/www/index.php";
        $SERVER["REQUEST_METHOD"] = $method;

        return $requestMockFactory->fromMock($SERVER, $data);
    }

    public function toJsonDate(DateTime $date = null)
    {
        return $date ? $date->format(BaseModel::DATE_FORMAT) : null;
    }
}
