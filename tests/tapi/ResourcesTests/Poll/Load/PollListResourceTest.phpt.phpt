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

class PollListResourceTest extends TapiTest {
    
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
        //nothing to set
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::type("array", $data);//returned polls array
        
        foreach ($data as $poll) {
            Assert::true(is_object($poll));
            Assert::type("int",$poll->id);
            Assert::true($poll->id > 0);
            Assert::type("int",$poll->createdById);
            Assert::true($poll->createdById > 0);
            Assert::type("string",$poll->createdAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $poll->createdAt)); //timezone correction check
            Assert::type("int",$poll->updatedById);
            Assert::true($poll->updatedById >= 0); //updatedById can be zero if nobody updated it yet
            Assert::type("string",$poll->updatedAt);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $poll->updatedAt)); //timezone correction check
            Assert::type("string",$poll->caption);
            if(property_exists($poll, "descriptionHtml")) Assert::type("string",$poll->descriptionHtml);
            Assert::type("bool",$poll->changeableVotes);
            Assert::type("bool",$poll->mainMenu);
            Assert::type("bool",$poll->anonymousResults);
            Assert::type("string",$poll->showResults);
            Assert::true(in_array($poll->showResults, ["NEVER", "ALWAYS", "AFTER_VOTE", "WHEN_CLOSED"]));
            Assert::type("string",$poll->status);
            Assert::true(in_array($poll->status, ["DESIGN", "OPENED", "CLOSED"]));
            Assert::type("string",$poll->resultRightName);
            Assert::type("string",$poll->voteRightName);
            //Assert::type("string",$poll->alienVoteRightName); // not always exists
            Assert::type("int",$poll->orderFlag);
            Assert::type("bool",$poll->canSeeResults);
            Assert::type("bool",$poll->canVote);
            Assert::type("bool",$poll->canAlienVote);
            Assert::type("bool",$poll->voted);
            Assert::type("bool",$poll->radio);
        }
    }
}

$test = new PollListResourceTest($container);
$test->run();
