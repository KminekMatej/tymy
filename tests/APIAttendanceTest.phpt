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

class APIAttendanceTest extends Tester\TestCase {

    private $container;
    private $login;
    private $loginObj;
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
    
    function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->team($GLOBALS["team"])
                ->setUsername($GLOBALS["username"])
                ->setPassword($GLOBALS["password"])
                ->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPlanFailsNoEventId(){
        $attendanceObj = new \Tymy\Attendance();
        $attendance = $attendanceObj
                ->team($GLOBALS["team"])
                ->plan();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPlanFailsNoPreStatus(){
        $attendanceObj = new \Tymy\Attendance();
        $attendance = $attendanceObj
                ->team($GLOBALS["team"])
                ->recId(147)
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
                ->presenter($mockPresenter)
                ->recId(147)
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
        $this->authenticator->setArr(["tym" => $GLOBALS["team"], "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $attendanceObj = new \Tymy\Attendance();
        $attendanceObj
                ->presenter($mockPresenter)
                ->recId(147)
                ->preStatus("YES")
                ->plan();
    }
    
    
    function test401relogin() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $tapiAuthenticator = new \App\Model\TymyUserManager($GLOBALS["team"]);
        $mockPresenter->getUser()->setAuthenticator($tapiAuthenticator);
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);
        
        $attendanceObj = new \Tymy\Attendance($mockPresenter->tapiAuthenticator, $mockPresenter);
        $attendanceObj
                ->presenter($mockPresenter)
                ->recId(147)
                ->preStatus("YES")
                ->plan();
        
        $logoutObj = new \Tymy\Logout($mockPresenter->tapiAuthenticator, $mockPresenter);
        $logoutObj ->logout();
        
        $attendanceObj2 = new \Tymy\Attendance($mockPresenter->tapiAuthenticator, $mockPresenter);
        $attendanceObj2
                ->presenter($mockPresenter)
                ->recId(147)
                ->preStatus("YES")
                ->plan();
    }
    
    function testPlanSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Event');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);
        
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
