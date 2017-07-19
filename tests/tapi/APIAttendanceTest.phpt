<?php
/**
 * TEST: Test Attendance on TYMY api
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

class APIAttendanceTest extends ITapiTest {

    /** @var \Tymy\Attendance */
    private $attendance;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->attendance;
    }
    
    protected function setUp() {
        $this->attendance = $this->container->getByType('Tymy\Attendance');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    function testPreStatus(){
        $field = "test" . md5(rand(0,100));
        $this->attendance->setPreStatus($field);
        Assert::equal($field, $this->attendance->getPreStatus());
    }
    
    function testPreDescription(){
        $field = "test" . md5(rand(0,100));
        $this->attendance->setPreDescription($field);
        Assert::equal($field, $this->attendance->getPreDescription());
    }
    
    function testPostStatus(){
        $field = "test" . md5(rand(0,100));
        $this->attendance->setPostStatus($field);
        Assert::equal($field, $this->attendance->getPostStatus());
    }
    
    function testPostDescription(){
        $field = "test" . md5(rand(0,100));
        $this->attendance->setPostDescription($field);
        Assert::equal($field, $this->attendance->getPostDescription());
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : PLAN */
    
    function testPlanFailsNoEventId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->attendance->reset()->plan();} , "\Tymy\Exception\APIException", "Event ID not set!");
    }
    
    function testPlanFailsNoPreStatus(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->plan();} , "\Tymy\Exception\APIException", "Pre status not set");
    }
    
    function testPlanNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->setPreStatus("YES")->plan();} , "Nette\Security\AuthenticationException", "Login failed.");
    }
    
    function testPlanRelogin() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->setPreStatus("YES")->plan();
        
        $logoutObj = $this->container->getByType('Tymy\Logout');
        $logoutObj ->logout();
        
        $this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->setPreStatus("NO")->plan();
    }
    
    function testPlanSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $allEvents = $this->container->getByType('Tymy\Events');
        $idActionToUpdateOn = $allEvents->setFrom(date("Ymd"))->getData()[0]->id;
         
        $this->attendance->reset()->recId($idActionToUpdateOn)
                ->setPreStatus("YES")
                ->setPreDescription("Tymyv2-AutoTest-yes")
                ->plan();
        Assert::type("array",$this->attendance->postParams[0]);
        Assert::same(4,count($this->attendance->postParams[0]));
        
        Assert::type("int",$this->attendance->postParams[0]["userId"]);
        Assert::same($this->user->getId(),$this->attendance->postParams[0]["userId"]);
        Assert::type("int",$this->attendance->postParams[0]["eventId"]);
        Assert::same($idActionToUpdateOn,$this->attendance->postParams[0]["eventId"]);
        Assert::type("string",$this->attendance->postParams[0]["preStatus"]);
        Assert::same("YES",$this->attendance->postParams[0]["preStatus"]);
        Assert::type("string",$this->attendance->postParams[0]["preDescription"]);
        Assert::same("Tymyv2-AutoTest-yes",$this->attendance->postParams[0]["preDescription"]); //Tested if POST params can be added as an array
        
        Assert::true(is_object($this->attendance));
        Assert::true(is_object($this->attendance->result));
        Assert::type("string",$this->attendance->result->status);
        Assert::same("OK",$this->attendance->result->status);

        //now check if the event is correctly filled
        $updatedEventObj = $this->container->getByType('Tymy\Event');
        $updatedEventObj
                ->reset()
                ->recId($idActionToUpdateOn)
                ->getResult();
        
        Assert::true(is_object($updatedEventObj));
        Assert::true(is_object($updatedEventObj->result));
        Assert::type("string",$updatedEventObj->result->status);
        Assert::same("OK",$updatedEventObj->result->status);
        
        Assert::true(is_object($updatedEventObj->result->data));
        $found = FALSE;
        foreach ($updatedEventObj->result->data->attendance as $att) {
            if($att->userId == $this->user->getId()){
                $found = TRUE;
                Assert::type("int",$att->eventId);
                Assert::same($idActionToUpdateOn,$att->eventId);
                Assert::type("string",$att->preStatus);
                Assert::same("YES",$att->preStatus);
                Assert::type("string",$att->preDescription);
                Assert::same("Tymyv2-AutoTest-yes",$att->preDescription);
            }
        }
        Assert::true($found);
    }

}

$test = new APIAttendanceTest($container);
$test->run();
