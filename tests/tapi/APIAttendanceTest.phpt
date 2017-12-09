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
        Assert::exception(function(){$this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->setPreStatus("YES")->plan();} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
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
        $idActionToUpdateOn = 125;
        $this->attendance->reset()->recId($idActionToUpdateOn)
                ->setPreStatus("YES")
                ->setPreDescription("Tymyv2-AutoTest-yes")
                ->plan();

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
    
    /* TAPI : CONFIRM */
    
    function testConfirmFailsNoEventId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->attendance->reset()->confirm(NULL);} , "\Tymy\Exception\APIException", "Event ID not set!");
    }
    
    function testConfirmFailsNoPostStatuses(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->confirm(NULL);} , "\Tymy\Exception\APIException", "Post statuses not set!");
    }
    
    function testConfirmNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->confirm([["userId" => $GLOBALS["testedTeam"]["testEventUserId"], "postStatus" => "YES"]]);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
    
    function testConfirmRelogin() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->confirm([["userId" => $GLOBALS["testedTeam"]["testEventUserId"], "postStatus" => "YES"]]);
        
        $logoutObj = $this->container->getByType('Tymy\Logout');
        $logoutObj ->logout();
        
        $this->attendance->reset()->recId($GLOBALS["testedTeam"]["testEventId"])->confirm([["userId" => $GLOBALS["testedTeam"]["testEventUserId"], "postStatus" => "YES"]]);
    }
    
    function testConfirmSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        
        $allEvents = $this->container->getByType('Tymy\Events');
        $idActionToUpdateOn = $GLOBALS["testedTeam"]["testEventId"];
         
        $this->attendance->reset()->recId($idActionToUpdateOn)
                ->confirm([["userId" => $GLOBALS["testedTeam"]["testEventUserId"], "postStatus" => "YES"]]);

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
            if($att->userId == $GLOBALS["testedTeam"]["testEventUserId"]){
                $found = TRUE;
                Assert::type("int",$att->eventId);
                Assert::same($idActionToUpdateOn,$att->eventId);
                Assert::type("string",$att->postStatus);
                Assert::same("YES",$att->postStatus);
            }
        }
        Assert::true($found);
    }
    
    function testResetWorks(){
        $this->attendance->setPreDescription("sdaf")->setPreStatus("shdfavk")->setPostDescription("jkhbk")->setPostStatus("zutvj");
        $this->attendance->reset();
        Assert::null($this->attendance->getPreDescription());
        Assert::null($this->attendance->getPostStatus());
        Assert::null($this->attendance->getPreDescription());
        Assert::null($this->attendance->getPostStatus());
        parent::resetParentTest($this->attendance);
    }

}

$test = new APIAttendanceTest($container);
$test->run();
