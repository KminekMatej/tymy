<?php

namespace Test\Tapi;

use Nette;
use Nette\Application\Request;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class EventDetailResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testEventId"]);
    }
    
    public function testToRunAsFirst(){
        parent::primaryTests();
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Event id not set!");
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::true(is_object($data));//returned event object
        
        Assert::type("int", $data->id);
        Assert::type("string", $data->caption);
        Assert::type("string", $data->type);
        Assert::type("string", $data->description);
        Assert::type("string", $data->closeTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->closeTime)); //timezone correction check
        Assert::type("string", $data->startTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->startTime)); //timezone correction check
        Assert::type("string", $data->endTime);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->endTime)); //timezone correction check
        Assert::type("string", $data->link);
        Assert::type("string", $data->place);

        Assert::type("bool", $data->canView);
        Assert::type("bool", $data->canPlan);
        Assert::type("bool", $data->canResult);
        Assert::type("bool", $data->inPast);
        Assert::type("bool", $data->inFuture);

        Assert::type("array", $data->attendance);
        Assert::true(count($data->attendance) > 0);

        
        foreach ($data->attendance as $att) {
            Assert::true(is_object($att));
            Assert::type("int", $att->userId);
            if (property_exists($att, "eventId")) {
                Assert::type("int", $att->eventId);
                Assert::type("string", $att->preStatus);
                if (property_exists($att, "preDescription"))
                    Assert::type("string", $att->preDescription);
                if (property_exists($att, "preUserMod"))
                    Assert::type("int", $att->preUserMod);
                if (property_exists($att, "preDatMod")){
                    Assert::type("string", $att->preDatMod);
                    Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $att->preDatMod)); //timezone correction check
                }
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

        Assert::true(is_object($data->myAttendance));
        
        Assert::true(is_object($data->eventType));
        Assert::type("int", $data->eventType->id);
        Assert::type("string", $data->eventType->code);
        Assert::type("string", $data->eventType->caption);
        Assert::type("int", $data->eventType->preStatusSetId);
        Assert::type("int", $data->eventType->postStatusSetId);
        Assert::type("array", $data->eventType->preStatusSet);
        foreach ($data->eventType->preStatusSet as $set) {
            Assert::type("int", $set->id);
            Assert::type("string", $set->code);
            Assert::type("string", $set->caption);
        }
    }
}

$test = new EventDetailResourceTest($container);
$test->run();
