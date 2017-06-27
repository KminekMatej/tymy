<?php
/**
 * TEST: Test Discussion on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIAttendanceTest extends TapiTestCase {

    private $container;
    private $authenticator;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPlanFailsNoEventId(){
        $attendanceObj = new \Tymy\Attendance();
        $attendance = $attendanceObj
                ->setSupplier($this->supplier)
                ->plan();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPlanFailsNoPreStatus(){
        $attendanceObj = new \Tymy\Attendance();
        $attendance = $attendanceObj
                ->setSupplier($this->supplier)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->plan();
    }

    
    /**
     * @throws Tymy\Exception\APIException
     */
    function test404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $attendanceObj = new \Tymy\Attendance();
        $attendanceObj
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
    }
    
    /**
     * @throws Tymy\Exception\APIAuthenticationException
     */
    function test401() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $attendanceObj = new \Tymy\Attendance();
        $attendanceObj
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
    }
    
    
    function test401relogin() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $tapiAuthenticator = new \App\Model\TapiAuthenticator($this->tapi_config);
        $mockPresenter->getUser()->setAuthenticator($tapiAuthenticator);
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $attendanceObj = new \Tymy\Attendance($mockPresenter->tapiAuthenticator, $mockPresenter);
        $attendanceObj
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
        
        $logoutObj = new \Tymy\Logout($mockPresenter->tapiAuthenticator, $mockPresenter);
        $logoutObj ->logout();
        
        $attendanceObj2 = new \Tymy\Attendance($mockPresenter->tapiAuthenticator, $mockPresenter);
        $attendanceObj2
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
    }
    
    function testPlanSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Event');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $allEvents = new \Tymy\Events($mockPresenter->tapiAuthenticator, $mockPresenter);
        $allEventsObj = $allEvents
                ->from(date("Ymd"))
                ->fetch();
        $idActionToUpdateOn = $allEventsObj[0]->id;

        $attendanceObj = new \Tymy\Attendance($mockPresenter->tapiAuthenticator, $mockPresenter);
        $attendanceObj->recId($idActionToUpdateOn)
                ->preStatus("YES")
                ->preDescription("Tymyv2-AutoTest-yes")
                ->plan();
        Assert::type("array",$attendanceObj->postParams[0]);
        Assert::same(4,count($attendanceObj->postParams[0]));
        
        Assert::type("int",$attendanceObj->postParams[0]["userId"]);
        Assert::same($this->login->id,$attendanceObj->postParams[0]["userId"]);
        Assert::type("int",$attendanceObj->postParams[0]["eventId"]);
        Assert::same($idActionToUpdateOn,$attendanceObj->postParams[0]["eventId"]);
        Assert::type("string",$attendanceObj->postParams[0]["preStatus"]);
        Assert::same("YES",$attendanceObj->postParams[0]["preStatus"]);
        Assert::type("string",$attendanceObj->postParams[0]["preDescription"]);
        Assert::same("Tymyv2-AutoTest-yes",$attendanceObj->postParams[0]["preDescription"]); //Tested if POST params can be added as an array
        
        Assert::true(is_object($attendanceObj));
        Assert::true(is_object($attendanceObj->result));
        Assert::type("string",$attendanceObj->result->status);
        Assert::same("OK",$attendanceObj->result->status);

        //now check if the event is correctly filled
        $updatedEventObj = new \Tymy\Event($mockPresenter->tapiAuthenticator, $mockPresenter);
        $updatedEventObj
                ->recId($idActionToUpdateOn)
                ->fetch();
        
        Assert::true(is_object($updatedEventObj));
        Assert::true(is_object($updatedEventObj->result));
        Assert::type("string",$updatedEventObj->result->status);
        Assert::same("OK",$updatedEventObj->result->status);
        
        Assert::true(is_object($updatedEventObj->result->data));
        $found = FALSE;
        foreach ($updatedEventObj->result->data->attendance as $att) {
            if($att->userId == $this->login->id){
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
