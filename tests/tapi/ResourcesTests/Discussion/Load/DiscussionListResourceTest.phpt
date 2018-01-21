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

class DiscussionListResourceTest extends TapiTest {
    
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
    
    public function testToRunAsFirst(){
        parent::primaryTests();
    }
    
    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        foreach ($data as $dis) {
            Assert::type("int",$dis->id);
            Assert::type("string",$dis->caption);
            Assert::type("string",$dis->description);
            Assert::type("string",$dis->readRightName);
            Assert::type("string",$dis->writeRightName);
            Assert::type("string",$dis->deleteRightName);
            Assert::type("string",$dis->stickyRightName);
            Assert::type("bool",$dis->publicRead);
            Assert::type("string",$dis->status);
            Assert::same("ACTIVE",$dis->status);
            Assert::type("bool",$dis->editablePosts);
            Assert::type("int",$dis->order);
            Assert::type("bool",$dis->canRead);
            Assert::type("bool",$dis->canWrite);
            Assert::type("bool",$dis->canDelete);
            Assert::type("bool",$dis->canStick);
            Assert::type("int", $dis->newPosts);
            if ($dis->newPosts > 0) {
                Assert::true(is_object($dis->newInfo));
                Assert::type("int", $dis->newInfo->newsCount);
                Assert::type("int", $dis->newInfo->discussionId);
                Assert::same($dis->id, $dis->newInfo->discussionId);
                Assert::type("string", $dis->newInfo->lastVisit);
                Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $dis->newInfo->lastVisit)); //timezone correction check
            }
        }
    }

}

$test = new DiscussionListResourceTest($container);
$test->run();
