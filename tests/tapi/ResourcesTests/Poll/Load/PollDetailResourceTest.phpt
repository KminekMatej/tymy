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

class PollDetailResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"]);
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Poll ID not set");
    }
    
    public function testItemNotFound(){
        $this->authenticateTapi($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->tapiObject->init()->setId(3190)->getData(TRUE);} , "\Tapi\Exception\APINotFoundException", "Záznam nenalezen");
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::true(is_object($data));//returned event object
        Assert::type("int",$data->id);
        
        Assert::type("int",$data->createdById);
        Assert::type("string",$data->createdAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->createdAt)); //timezone correction check
        Assert::type("int",$data->updatedById);
        Assert::type("string",$data->updatedAt);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->updatedAt)); //timezone correction check
        Assert::type("string",$data->caption);
        Assert::type("string",$data->description);
        if(property_exists($data, "minItems")){
            Assert::type("int",$data->minItems);
            Assert::true($data->minItems > 0 || $data->minItems == -1);
        }
        if(property_exists($data, "maxItems")){
            Assert::type("int",$data->maxItems);
            Assert::true($data->maxItems > 0 || $data->maxItems == -1);
            Assert::true($data->maxItems >= $data->minItems);
        }
        Assert::type("bool",$data->changeableVotes);
        Assert::type("bool",$data->mainMenu);
        Assert::type("bool",$data->anonymousResults);
        Assert::type("string",$data->showResults);
        Assert::true(in_array($data->showResults, ["NEVER", "ALWAYS", "AFTER_VOTE", "WHEN_CLOSED"]));
        Assert::type("string",$data->status);
        Assert::true(in_array($data->status, ["DESIGN", "OPENED", "CLOSED"]));
        Assert::type("string",$data->resultRightName);
        Assert::type("string",$data->voteRightName);
        Assert::type("int",$data->orderFlag);
        
        Assert::type("array",$data->options);
        foreach ($data->options as $opt) {
            Assert::type("int",$opt->id);
            Assert::true($opt->id > 0);
            Assert::type("int",$opt->pollId);
            Assert::type("string",$opt->caption);
            Assert::type("string",$opt->type);
            Assert::true(in_array($opt->type, ["TEXT", "NUMBER", "BOOLEAN"]));
        }
        
        foreach ($data->votes as $vote) {
            Assert::type("int",$vote->pollId);
            Assert::type("int",$vote->userId);
            Assert::true($vote->userId > 0);
            
            //check option
            Assert::type("int",$vote->optionId);
            Assert::true($vote->optionId > 0);
            
            Assert::type("int",$vote->updatedById);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $vote->updatedAt)); //timezone correction check
        }
    }
    
    public function testFetchAnonymousSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $pollId = $GLOBALS["testedTeam"]["testAnonymousPollId"];
        $data = $this->tapiObject->init()->setId($pollId)->getData(TRUE);

        Assert::true(is_object($data));
        
        Assert::type("int",$data->id);
        Assert::same($pollId,$data->id);
        
        Assert::equal(true,$data->anonymousResults);
        
        Assert::type("array",$data->options);
        print_r($data->options);
        foreach ($data->options as $opt) {
            Assert::true(!property_exists($opt, "updatedById"));
            Assert::true(!property_exists($opt, "updatedBy"));
        }
    }
}

$test = new PollDetailResourceTest($container);
$test->run();
