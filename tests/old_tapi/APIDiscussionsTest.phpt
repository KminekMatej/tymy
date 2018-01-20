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

Tester\Environment::lock('tapi', __DIR__ . '/../lockdir'); //belong to the group of tests which should not run paralelly

class APIDiscussionsTest extends ITapiTest {

    /** @var \Tymy\Discussions */
    private $discussions;

    private $createdDiscussionId;

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
    
    public function idWebnameProvider(){
        return [
            ["tymova-diskuze", 1],
            ["testovaci-diskuze", 2],
            ["oznameni", 3],
            ["api", 4],
            ["bugy", 5]
        ];
    }
    
    /** @dataProvider idWebnameProvider */
    function testIdWebnameParse($webname, $idExpected){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::equal($idExpected, $this->discussions->getIdFromWebname($webname));
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

    /* TAPI : CREATE */
    
    function testCreateFailsNoCaption(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussions->reset()->create([NULL]);} , "\Tymy\Exception\APIException", "Caption not set!");
    }
    
    function testCreateFailsNoRights(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->discussions->reset()->create(["caption" => "Autotest " . rand(0, 100)]);} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testCreateSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $discussionCaption = "Autotest " . rand(0, 100);
        $this->discussions->reset()->create(["caption" => $discussionCaption]);
        
        Assert::true(is_object($this->discussions));
        Assert::true(is_object($this->discussions->result));
        Assert::type("string",$this->discussions->result->status);
        Assert::same("OK",$this->discussions->result->status);
        Assert::type("int",$this->discussions->result->data->id);
        $this->createdDiscussionId = $this->discussions->result->data->id;
        Assert::type("string",$this->discussions->result->data->caption);
        Assert::type("string",$this->discussions->result->data->readRightName);
        Assert::type("string",$this->discussions->result->data->writeRightName);
        Assert::type("string",$this->discussions->result->data->deleteRightName);
        Assert::type("string",$this->discussions->result->data->stickyRightName);
        Assert::type("bool",$this->discussions->result->data->publicRead);
        Assert::same(FALSE, $this->discussions->result->data->publicRead);
        Assert::type("string",$this->discussions->result->data->status);
        Assert::same("ACTIVE", $this->discussions->result->data->status);
        Assert::type("bool",$this->discussions->result->data->editablePosts);
    }
    
    /* TAPI : EDIT */
    
    function testEditFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussions->reset()->edit(NULL);} , "\Tymy\Exception\APIException", "Discussion ID not set!");
    }
    
    function testEditFailsNoFields() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussions->reset()->recId($this->createdDiscussionId)->edit(NULL);} , "\Tymy\Exception\APIException", "Fields to edit not set!");
    }
    
    function testEditFailsNoRights() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->discussions->reset()->recId($this->createdDiscussionId)->edit(["caption" => "Autotest " . rand(100, 200)]);} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testEditSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $discussionCaption = "Autotest " . rand(100, 200);
        $this->discussions->reset()->recId($this->createdDiscussionId)->edit(["caption" => $discussionCaption]);
        
        Assert::true(is_object($this->discussions));
        Assert::true(is_object($this->discussions->result));
        Assert::type("string",$this->discussions->result->status);
        Assert::same("OK",$this->discussions->result->status);
        Assert::equal($this->createdDiscussionId,$this->discussions->result->data->id);
        Assert::type("string",$this->discussions->result->data->caption);
        Assert::equal($discussionCaption,$this->discussions->result->data->caption);
    }
    
    /* TAPI : DELETE */
    
    function testDeleteFailsNoRecId() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->discussions->reset()->delete();} , "\Tymy\Exception\APIException", "Discussion ID not set!");
    }
    
    function testDeleteFailsNoRights() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->discussions->reset()->recId($this->createdDiscussionId)->delete();} , "\Tymy\Exception\APIException", "Permission denied!");
    }
    
    function testDeleteSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->discussions->reset()->recId($this->createdDiscussionId)->delete();
        
        Assert::true(is_object($this->discussions));
        Assert::true(is_object($this->discussions->result));
        Assert::type("string",$this->discussions->result->status);
        Assert::same("OK",$this->discussions->result->status);
    }
    
}

$test = new APIDiscussionsTest($container);
$test->run();
