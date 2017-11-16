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

class APIUserTest extends ITapiTest {

    /** @var \Tymy\User */
    private $tapi_user;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->tapi_user;
    }
    
    protected function setUp() {
        $this->tapi_user = $this->container->getByType('Tymy\User');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testSelectFailsNoRecId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_user->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "User ID not set!");
    }
    
    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_user->reset()->recId(1)->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
    
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->getResult(TRUE);
        
        Assert::true(is_object($this->tapi_user));
        Assert::true(is_object($this->tapi_user->result));
        Assert::type("string",$this->tapi_user->result->status);
        Assert::same("OK",$this->tapi_user->result->status);
        
        Assert::type("int",$this->tapi_user->result->data->id);
        Assert::true($this->tapi_user->result->data->id > 0);
        Assert::type("string",$this->tapi_user->result->data->login);
        Assert::type("bool",$this->tapi_user->result->data->canLogin);
        Assert::type("bool",$this->tapi_user->result->data->canEditCallName);
        Assert::type("string",$this->tapi_user->result->data->lastLogin);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->tapi_user->result->data->lastLogin)); //timezone correction check
        Assert::type("string",$this->tapi_user->result->data->status);
        Assert::true(in_array($this->tapi_user->result->data->status, ["PLAYER", "MEMBER", "SICK"]));
        if (property_exists($this->tapi_user->result->data, "roles")) {
            Assert::type("array", $this->tapi_user->result->data->roles);
            foreach ($this->tapi_user->result->data->roles as $role) {
                Assert::type("string", $role);
            }
        }
        Assert::type("string",$this->tapi_user->result->data->firstName);
        Assert::type("string",$this->tapi_user->result->data->lastName);
        Assert::type("string",$this->tapi_user->result->data->callName);
        Assert::type("string",$this->tapi_user->result->data->language);
        Assert::type("string",$this->tapi_user->result->data->email);
        Assert::type("string",$this->tapi_user->result->data->jerseyNumber);
        if(property_exists($this->tapi_user->result->data, "gender"))
            Assert::type("string",$this->tapi_user->result->data->gender);
        Assert::type("string",$this->tapi_user->result->data->street);
        Assert::type("string",$this->tapi_user->result->data->city);
        Assert::type("string",$this->tapi_user->result->data->zipCode);
        Assert::type("string",$this->tapi_user->result->data->phone);
        Assert::type("string",$this->tapi_user->result->data->phone2);
        if(property_exists($this->tapi_user->result->data, "birthDate"))
            Assert::type("string",$this->tapi_user->result->data->birthDate);
        Assert::type("int",$this->tapi_user->result->data->nameDayMonth);
        Assert::type("int",$this->tapi_user->result->data->nameDayDay);
        Assert::type("string",$this->tapi_user->result->data->pictureUrl);
        Assert::type("string",$this->tapi_user->result->data->fullName);
        Assert::type("string",$this->tapi_user->result->data->displayName);
        Assert::type("string",$this->tapi_user->result->data->webName);
        Assert::type("int",$this->tapi_user->result->data->errCnt);
        Assert::true($this->tapi_user->result->data->errCnt>= 0);
        Assert::type("array",$this->tapi_user->result->data->errFls);
        foreach ($this->tapi_user->result->data->errFls as $errF) {
            Assert::type("string",$errF);
        }
        
    }
    
    /* TAPI : EDIT */
    
    function testEditFailsNoRecId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_user->reset()->edit([NULL]);} , "\Tymy\Exception\APIException", "User ID not set!");
    }
    
    function testEditFailsNoFields(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->edit(NULL);} , "\Tymy\Exception\APIException", "Fields to edit not set!");
    }
    
    function testEditSuccess(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $newCallName = "Callname " . rand(0, 200);
        $this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->edit(["callName" => $newCallName]);
        
        Assert::true(is_object($this->tapi_user));
        Assert::true(is_object($this->tapi_user->result));
        Assert::type("string",$this->tapi_user->result->status);
        Assert::same("OK",$this->tapi_user->result->status);
        
        Assert::equal($GLOBALS["testedTeam"]["testUserId"],$this->tapi_user->result->data->id);
        Assert::equal($newCallName,$this->tapi_user->result->data->callName);
    }
    
    /* TAPI : AVATAR */
    
    function testAvatarFailsNoRecId(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_user->reset()->setAvatar("12345");} , "\Tymy\Exception\APIException", "User ID not set!");
    }
    
    function testAvatarFailsWrongImg(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->setAvatar("12345");} , "\Tymy\Exception\APIException", "Avatar not set!");
        Assert::exception(function(){$this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->setAvatar(NULL);} , "\Tymy\Exception\APIException", "Avatar not set!");
    }
    
    function testAvatarSuccess(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->getResult();
        $origImgUrl = $this->supplier->getTymyRoot() . $this->tapi_user->result->data->pictureUrl;
        $origImgExt = pathinfo($origImgUrl, PATHINFO_EXTENSION);
        $origImgB64 = 'data:image/' . $origImgExt . ';base64,' . base64_encode(file_get_contents($origImgUrl));
        
        $testImgB64 = $GLOBALS["testedTeam"]["avatarB64"];
        
        $this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->setAvatar($testImgB64);
        
        Assert::true(is_object($this->tapi_user));
        Assert::true(is_object($this->tapi_user->result));
        Assert::type("string",$this->tapi_user->result->status);
        Assert::same("OK",$this->tapi_user->result->status);
        
        $this->tapi_user->reset()->recId($GLOBALS["testedTeam"]["testUserId"])->setAvatar($origImgB64);
    }
    
}

$test = new APIUserTest($container);
$test->run();
