<?php

/**
 * TEST: Test Event detail on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';
Tester\Environment::skip('Temporary skipping');
if (in_array(basename(__FILE__, '.phpt'), $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIEventTest extends ITapiTest {

    private $authenticator;
    private $container;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    function setUp() {
        parent::setUp();
        $this->initTapiConfiguration($this->container);
        $this->authenticator = new \App\Model\TestAuthenticator();
    }

    function tearDown() {
        parent::tearDown();
    }

    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchNotLoggedInFailsRecIdNotSet() {
        $eventObj = new \Tymy\Event();
        $eventObj->setSupplier($this->supplier)->fetch();
    }

    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $eventObj = new \Tymy\Event();
        $eventObj->setPresenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }

    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $eventObj = new \Tymy\Event();
        $eventObj->setPresenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }

    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Event');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $eventId = $GLOBALS["testedTeam"]["testEventId"];
        $eventObj = new \Tymy\Event($mockPresenter->tapiAuthenticator, $mockPresenter);
        $eventObj->recId($eventId)
                ->fetch();
        Assert::true(is_object($eventObj));
        Assert::true(is_object($eventObj->result));
        Assert::type("string", $eventObj->result->status);
        Assert::same("OK", $eventObj->result->status);
        Assert::type("int", $eventObj->result->data->id);
        Assert::same($eventId, $eventObj->result->data->id);

        Assert::type("string", $eventObj->result->data->caption);
        Assert::type("string", $eventObj->result->data->type);
        Assert::type("string", $eventObj->result->data->description);
        Assert::type("string", $eventObj->result->data->closeTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $eventObj->result->data->closeTime)); //timezone correction check
        Assert::type("string", $eventObj->result->data->startTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $eventObj->result->data->startTime)); //timezone correction check
        Assert::type("string", $eventObj->result->data->endTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $eventObj->result->data->endTime)); //timezone correction check
        Assert::type("string", $eventObj->result->data->link);
        Assert::type("string", $eventObj->result->data->place);

        Assert::type("bool", $eventObj->result->data->canView);
        Assert::type("bool", $eventObj->result->data->canPlan);
        Assert::type("bool", $eventObj->result->data->canResult);
        Assert::type("bool", $eventObj->result->data->inPast);
        Assert::type("bool", $eventObj->result->data->inFuture);

        Assert::type("array", $eventObj->result->data->attendance);
        Assert::true(count($eventObj->result->data->attendance) > 0);

        foreach ($eventObj->result->data->attendance as $att) {
            Assert::true(is_object($att));
            Assert::type("int", $att->userId);
            if (property_exists($att, "eventId")) {
                Assert::type("int", $att->eventId);
                Assert::same($eventId, $att->eventId);
                Assert::type("string", $att->preStatus);
                if (property_exists($att, "preDescription"))
                    Assert::type("string", $att->preDescription);
                Assert::type("int", $att->preUserMod);
                Assert::type("string", $att->preDatMod);
                Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $att->preDatMod)); //timezone correction check
            }

            Assert::true(is_object($att->user));
            Assert::type("int", $att->user->id);
            Assert::true($att->user->id > 0);
            Assert::type("string", $att->user->login);
            Assert::type("string", $att->user->callName);
            Assert::type("string", $att->user->pictureUrl);
            Assert::true(!property_exists($att->user, "gender"));
        }

        Assert::true(is_object($eventObj->result->data->myAttendance));
        
        Assert::true(is_object($eventObj->result->data->eventType));
        Assert::type("int", $eventObj->result->data->eventType->id);
        Assert::type("string", $eventObj->result->data->eventType->code);
        Assert::type("string", $eventObj->result->data->eventType->caption);
        Assert::type("int", $eventObj->result->data->eventType->preStatusSetId);
        Assert::type("int", $eventObj->result->data->eventType->postStatusSetId);
        Assert::type("array", $eventObj->result->data->eventType->preStatusSet);
        foreach ($eventObj->result->data->eventType->preStatusSet as $set) {
            Assert::type("int", $set->id);
            Assert::type("string", $set->code);
            Assert::type("string", $set->caption);
        }
    }

}

$test = new APIEventTest($container);
$test->run();
