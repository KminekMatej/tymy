<?php
/**
 * TEST: Test Discussion on TYMY api
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

class APIRegisterTest extends ITapiTest {

    /** @var \Tymy\User */
    private $tapi_user;

    /** @var \Tymy\Register */
    private $tapi_register;
    
    private $registeredId;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->tapi_register;
    }
    
    protected function setUp() {
        $this->tapi_register = new \Tymy\Register($this->container->getByType('App\Model\Supplier'));
        $this->tapi_user = $this->container->getByType('Tymy\User');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    //none getters, only setters
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : REGISTER */

    function testRegisterFailsNoLogin(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_register->reset()->register()->getResult();} , "\Tymy\Exception\APIException", "Login not set!");
    }
    
    function testRegisterFailsNoPassword(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_register->reset()->setLogin(md5("" . rand(0, 1000)))->register()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Password not set!");
    }
    
    function testRegisterFailsNoEmail(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_register->reset()->setLogin(md5("" . rand(0, 1000)))->setPassword("123456789")->register()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Email not set!");
    }
    
    function testRegisterSuccess() {
        if(!$GLOBALS["testedTeam"]["invasive"])
            return null;
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $login = substr(md5("" . rand(0, 1000)),0,15);
        $password = md5("" . rand(0, 1000));
        $email = "test@example.com";
        $firstName = substr(md5("" . rand(0, 1000)),0,20);
        $lastName = substr(md5("" . rand(0, 1000)),0,20);
        $admin_note = md5("" . rand(0, 1000));
        $createdResult = $this->tapi_register
                ->reset()
                ->setLogin($login)
                ->setPassword($password)
                ->setEmail($email)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setAdmin_note($admin_note)
                ->register()
                ->getResult();
        
        Assert::true(is_object($createdResult));
        Assert::type("string",$createdResult->status);
        Assert::same("OK",$createdResult->status);
        Assert::true(is_object($createdResult->data));
        
        $createdUser = $createdResult->data;
        
        
        Assert::type("int",$createdUser->id);
        Assert::true($createdUser->id > 0);
        Assert::type("string",$createdUser->login);
        Assert::same($createdUser->login, strtoupper($login));
        Assert::type("bool",$createdUser->canLogin);
        Assert::true($createdUser->canLogin);
        Assert::type("string",$createdUser->createdAt);
        Assert::true(!property_exists($createdUser, "lastLogin"));
        Assert::equal("INIT",$createdUser->status);
        Assert::same($createdUser->firstName, $firstName);
        Assert::same($createdUser->lastName, $lastName);
        Assert::same($createdUser->callName, substr($firstName . " " . $lastName, 0, 30));
        Assert::same($createdUser->displayName, substr($firstName . " " . $lastName, 0, 30));
        Assert::same($createdUser->email, $email);
        Assert::type("string",$createdUser->jerseyNumber);
        Assert::same($createdUser->jerseyNumber, "");
        Assert::type("string",$createdUser->gender);
        Assert::same($createdUser->gender, "MALE");
        Assert::type("string",$createdUser->pictureUrl);
        Assert::type("string",$createdUser->note);
        
        //delete registered user
        $this->tapi_user->reset()->recId($createdUser->id)->edit(["status" => "DELETED"])->getResult();
    }
    
}

$test = new APIRegisterTest($container);
$test->run();
