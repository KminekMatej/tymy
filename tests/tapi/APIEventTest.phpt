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

if (in_array(basename(__FILE__, '.phpt'), $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIEventTest extends ITapiTest {

    /** @var \Tymy\Event */
    private $event;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->event;
    }
    
    protected function setUp() {
        $this->event = $this->container->getByType('Tymy\Event');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testSelectNotLoggedInFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->event->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Event ID not set!");
    }

    function testFetchNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->event->reset()->recId(1)->getResult(TRUE);} , "Nette\Security\AuthenticationException", "Login failed.");
    }

    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $eventId = $GLOBALS["testedTeam"]["testEventId"];
        $this->event->reset()->recId($eventId)->getResult(TRUE);
        
        Assert::true(is_object($this->event));
        Assert::true(is_object($this->event->result));
        Assert::type("string", $this->event->result->status);
        Assert::same("OK", $this->event->result->status);
        Assert::type("int", $this->event->result->data->id);
        Assert::same($eventId, $this->event->result->data->id);

        Assert::type("string", $this->event->result->data->caption);
        Assert::type("string", $this->event->result->data->type);
        Assert::type("string", $this->event->result->data->description);
        Assert::type("string", $this->event->result->data->closeTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->event->result->data->closeTime)); //timezone correction check
        Assert::type("string", $this->event->result->data->startTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->event->result->data->startTime)); //timezone correction check
        Assert::type("string", $this->event->result->data->endTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->event->result->data->endTime)); //timezone correction check
        Assert::type("string", $this->event->result->data->link);
        Assert::type("string", $this->event->result->data->place);

        Assert::type("bool", $this->event->result->data->canView);
        Assert::type("bool", $this->event->result->data->canPlan);
        Assert::type("bool", $this->event->result->data->canResult);
        Assert::type("bool", $this->event->result->data->inPast);
        Assert::type("bool", $this->event->result->data->inFuture);

        Assert::type("array", $this->event->result->data->attendance);
        Assert::true(count($this->event->result->data->attendance) > 0);

        foreach ($this->event->result->data->attendance as $att) {
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
            if(property_exists($att->user, "gender")){
                Assert::contains($att->user->gender, ["MALE","FEMALE"]);
            }
        }

        Assert::true(is_object($this->event->result->data->myAttendance));
        
        Assert::true(is_object($this->event->result->data->eventType));
        Assert::type("int", $this->event->result->data->eventType->id);
        Assert::type("string", $this->event->result->data->eventType->code);
        Assert::type("string", $this->event->result->data->eventType->caption);
        Assert::type("int", $this->event->result->data->eventType->preStatusSetId);
        Assert::type("int", $this->event->result->data->eventType->postStatusSetId);
        Assert::type("array", $this->event->result->data->eventType->preStatusSet);
        foreach ($this->event->result->data->eventType->preStatusSet as $set) {
            Assert::type("int", $set->id);
            Assert::type("string", $set->code);
            Assert::type("string", $set->caption);
        }
    }

}

$test = new APIEventTest($container);
$test->run();
