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

class EventCreateResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::POST;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setEventTypesArray($this->mockEventTypes())->setEventsArray($this->mockEvents());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Events array not set");
        Assert::exception(function() {
            $eventMock = $this->mockEvents();
            $this->tapiObject->init()->setEventsArray($eventMock)->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Event types array not set");

        Assert::exception(function() {
            $eventMock = [[
            "type" => "TRA",
            "caption" => "Autotest event",
            "description" => "Událost vytvořená autotestem, měla by být zase autotestem smazána",
            "endTime" => date("c"),
            "closeTime" => date("c"),
            "place" => "Kdesi na východě",
            "link" => "http://www.tymy.cz",
            ]];
            $this->tapiObject->init()->setEventsArray($eventMock)->setEventTypesArray($this->mockEventTypes())->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Event start time not set");

        Assert::exception(function() {
            $eventMock = [[
            "caption" => "Autotest event",
            "description" => "Událost vytvořená autotestem, měla by být zase autotestem smazána",
            "startTime" => date("c"),
            "endTime" => date("c"),
            "closeTime" => date("c"),
            "place" => "Kdesi na východě",
            "link" => "http://www.tymy.cz",
            ]];
            $this->tapiObject->init()->setEventsArray($eventMock)->setEventTypesArray($this->mockEventTypes())->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Event type not set");
        Assert::exception(function() {
            $eventMock = [[
            "type" => "XXX",
            "caption" => "Autotest event",
            "description" => "Událost vytvořená autotestem, měla by být zase autotestem smazána",
            "startTime" => date("c"),
            "endTime" => date("c"),
            "closeTime" => date("c"),
            "place" => "Kdesi na východě",
            "link" => "http://www.tymy.cz",
            ]];
            $this->tapiObject->init()->setEventsArray($eventMock)->setEventTypesArray($this->mockEventTypes())->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Unrecognized type");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        $deleter = $this->container->getByType("Tapi\EventDeleteResource");
        foreach ($data as $event) {
            $deleter->init()->setId($event->id)->perform();
        }
    }

    private function mockEvents(){
        $caption = "Autotest event";
        return [[
            "type"=>"TRA",
            "caption"=>$caption,
            "description"=>"Událost vytvořená autotestem, měla by být zase autotestem smazána",
            "startTime"=>date("c"),
            "endTime"=>date("c"),
            "closeTime"=>date("c"),
            "place"=>"Kdesi na východě",
            "link"=>"http://www.tymy.cz",
        ]];
    }
    
    private function mockEventTypes(){
        return ["TRA"=>"TRA","RUN"=>"RUN","TST1"=>"TST1"];
    }
}

$test = new EventCreateResourceTest($container);
$test->run();
