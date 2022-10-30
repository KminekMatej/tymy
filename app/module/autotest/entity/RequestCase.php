<?php

namespace Tymy\Module\Autotest;

use Nette\Application\BadRequestException;
use Nette\Application\PresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\InvalidStateException;
use Nette\Neon\Neon;
use Nette\Routing\Router;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tester\Environment;
use Tester\TestCase;
use Tracy\Debugger;
use Tymy\Bootstrap;
use Tymy\Module\Authentication\Manager\AuthenticationManager;
use Tymy\Module\Autotest\Entity\Assert;
use Tymy\Module\Core\Manager\Responder;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Api\BasePresenter;
use const TEAM_DIR;
use const TEST_DIR;


/**
 * Envelope class for all api testing classes
 *
 * @author kminekmatej, 10.3.2019
 */
abstract class RequestCase extends TestCase
{
    public const REGEX_JSON_DATE = '#^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}#';

    protected JsonResponse $jsonResponse;
    protected Explorer $database;
    protected User $user;
    protected array $config;
    protected array $moduleConfig;
    protected RecordManager $recordManager;
    private Router $router;
    private PresenterFactory $presenterFactory;
    protected AuthenticationManager $authenticationManager;
    protected Responder $responder;
    private IRequest $httpRequest;
    private MockRequestFactory $httpRequestFactory;

    /** @var RequestLog[] */
    private array $logs = [];

    public function __construct(protected Container $container)
    {
        define('TEST_DIR', Bootstrap::normalizePath(Bootstrap::MODULES_DIR . "/autotest"));
        define('WWW_DIR', Bootstrap::normalizePath(TEAM_DIR . "/www"));
        $this->user = $this->container->getByType(User::class);
        $this->authenticationManager = $this->container->getByType(AuthenticationManager::class);
        $this->presenterFactory = $this->container->getService("application.presenterFactory");
        $this->responder = $this->container->getService("Responder");
        $this->database = $this->container->getService("database.team.explorer");
        $this->router = $this->container->getService("router");
        $this->config = Neon::decode(file_get_contents(TEST_DIR . '/autotest.records.map.neon'));
        $this->moduleConfig = $this->config[$this->getModule()] ?? [];
        $this->recordManager = new RecordManager($this, $this->config);
        $this->httpRequest = $this->container->getService("http.request");
        $this->httpRequestFactory = $this->container->getService("http.requestFactory");
        Environment::setup();
    }

    /**
     * Function should return string of module name, preferably from constant
     */
    abstract public function getModule(): string;

    protected function getBasePath(): string
    {
        return $this->getModule();
    }

    abstract public function createRecord();

    public function deleteRecord($recordId): void
    {
        $this->recordManager->deleteRecord($this->getBasePath(), $recordId);
    }

    abstract public function mockRecord();

    /**
     * @return mixed[]
     */
    abstract protected function mockChanges(): array;

    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    protected function tearDown(): void
    {
        //process request logs and save them to file
        if (empty($this->logs)) {
            return;
        }

        $fp = fopen(Debugger::$logDirectory . '/requests.log', 'a+');
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
            } elseif ($requestLog->getHttpResponseCode() == $requestLog->getExpectCode()) {
                $codeStr = ", code: {$requestLog->getExpectCode()}";
                $success = true;
            } else {
                $codeStr = ", code: {$requestLog->getHttpResponseCode()}/{$requestLog->getExpectCode()}";
                $success = false;
            }
            assert($requestLog instanceof RequestLog);
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
                $string .= ", data: " . \json_encode($data, JSON_THROW_ON_ERROR);
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

        $changedData = $this->request($this->getBasePath() . "/" . $recordId, "PUT", $changes)->expect(200, "array")->getData();

        $this->assertObjectEquality($changes, $changedData);
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
        $url = "/api/" . trim($url, "/ ");

        $this->logs[] = $log = new RequestLog($method, $url, $data);

        $httpRequest = $this->httpRequestFactory->from($url, $method, \json_encode($data));
        $request = $this->createInitialRequest($httpRequest);

        Assert::type(Request::class, $request, "No route found for url $url");
        $presenterMock = $this->loadPresenter($request->getPresenterName());
        $presenterMock->setRequestData($data);
        $this->responder->presenterMock = $presenterMock;
        $response = $presenterMock->run($request);
        $httpResponse = $presenterMock->getHttpResponse();
        if (!$responseClass) {
            Assert::type(JsonResponse::class, $response);
            assert($response instanceof JsonResponse);
            return new SimpleResponse($httpResponse->getCode(), ($response->getPayload()["status"] == "OK" ? ($response->getPayload()["data"] ?? null) : null), $request, $httpResponse, $response, $presenterMock, $log);
        } elseif ($responseClass == TextResponse::class) {
            Assert::type($responseClass, $response);
            assert($response instanceof TextResponse);
            return new SimpleResponse($httpResponse->getCode(), $response->getSource(), $request, $httpResponse, $response, $presenterMock, $log);
        } else {
            Assert::type($responseClass, $response);
            return new SimpleResponse($httpResponse->getCode(), null, $request, $httpResponse, $response, $presenterMock, $log);
        }
    }

    private function createInitialRequest(IRequest $httpRequest): Request
    {
        $params = $this->router->match($httpRequest);
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

    public function authorizeUser($userName = null, $password = null)
    {
        $this->user->logout(true);
        $this->user->setAuthenticator($this->authenticationManager);
        $this->user->login($userName ?: $this->config["user_test_login"], $password ?: $this->config["user_test_pwd"]);

        Assert::true($this->user->isLoggedIn());
        Assert::equal($this->user->getId(), $this->config["user_test_id"]);
    }

    public function authorizeAdmin($userName = null, $password = null)
    {
        $this->user->logout(true);
        $this->user->setAuthenticator($this->authenticationManager);
        $this->user->login($userName ?: $this->config["user_admin_login"], $password ?: $this->config["user_admin_pwd"]);

        Assert::true($this->user->isLoggedIn());
        if (empty($userName)) {
            Assert::equal($this->user->getId(), $this->config["user_admin_id"]);
        }
    }

    /**
     * Assert that all props of two objects are equal.
     * WARNING: Datetime, sent in local timezone are stored to DB in local timezone, but API responds them in UTC timezone
     *
     * @param array $original
     * @param array $new
     * @param array|null $skip Fields to skip
     */
    public function assertObjectEquality(array $original, array $new, ?array $skip = null)
    {
        foreach ($original as $key => $value) {
            if ($skip && ((is_array($skip) && in_array($key, $skip)) || $key == $skip)) {
                continue;
            }

            Assert::hasKey($key, $new);
            Assert::equal($value, $new[$key], "Error on `$key` field");
        }
    }

    protected function loadPresenter(string $name): BasePresenter
    {
        $presenter = $this->presenterFactory->createPresenter($name);
        assert($presenter instanceof BasePresenter);
        $presenter->autoCanonicalize = false;
        return $presenter;
    }

    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function toJsonDate(DateTime $date = null)
    {
        return $date !== null ? $date->format(BaseModel::DATE_FORMAT) : null;
    }
}
