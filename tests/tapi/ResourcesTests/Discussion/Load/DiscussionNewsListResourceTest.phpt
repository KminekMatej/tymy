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

class DiscussionNewsListResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
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
        Assert::type("array",$data);
        
        foreach ($data as $new) {
            Assert::type("int",$new->id);
            Assert::type("int",$new->newPosts);
        }
    }

}

$test = new DiscussionNewsListResourceTest($container);
$test->run();
