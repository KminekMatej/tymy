<?php
/**
 * TEST: Test Discussions on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIDiscussionsTest extends ITapiTest {

    /** @var \Tymy\Discussions */
    private $discussions;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->discussions;
    }
    
    protected function setUp() {
        $this->discussions = $this->container->getByType('Tymy\Discussions');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    function testWithNew(){
        Assert::equal(FALSE, $this->discussions->getWithNew());
        $withNew = TRUE;
        $this->discussions->setWithNew($withNew);
        Assert::equal($withNew, $this->discussions->getWithNew());
        $withNew = FALSE;
        $this->discussions->setWithNew($withNew);
        Assert::equal($withNew, $this->discussions->getWithNew());
        
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */
    
    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussions->setWithNew(TRUE)->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
        
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->discussions->setWithNew(FALSE)->getResult(TRUE);
        
        Assert::true(is_object($this->discussions));
        Assert::true(is_object($this->discussions->result));
        Assert::type("string",$this->discussions->result->status);
        Assert::same("OK",$this->discussions->result->status);
        Assert::type("array",$this->discussions->result->data);
        
        foreach ($this->discussions->result->data as $dis) {
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
            Assert::true(!property_exists($dis, "newInfo"));
        }
    }
    
    function testSelectWithNewSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $this->discussions->setWithNew(TRUE)->getResult(TRUE);

        Assert::true(is_object($this->discussions));
        Assert::true(is_object($this->discussions->result));
        Assert::type("string",$this->discussions->result->status);
        Assert::same("OK",$this->discussions->result->status);
        Assert::type("array",$this->discussions->result->data);
        
        foreach ($this->discussions->result->data as $dis) {
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

$test = new APIDiscussionsTest($container);
$test->run();
