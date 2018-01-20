<?php
/**
 * TEST: Test Users on TYMY api
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

class APIUsersTest extends ITapiTest{

    /** @var \Tymy\Users */
    private $users;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->users;
    }
    
    protected function setUp() {
        $this->users = $this->container->getByType('Tymy\Users');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->users->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
        
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->users->reset()->getResult(TRUE);
        
        Assert::same(1, count($this->users->getUriParams()));
        
        Assert::true(is_object($this->users));
        Assert::true(is_object($this->users->result));
        Assert::type("string",$this->users->result->status);
        Assert::same("OK",$this->users->result->status);
        
        Assert::type("array",$this->users->result->data);
        
        foreach ($this->users->result->data as $u) {
            Assert::true(is_object($u));
            Assert::type("int",$u->id);
            Assert::type("string",$u->login);
            Assert::type("bool",$u->canLogin);
            Assert::type("bool",$u->canEditCallName);
            if(property_exists($u, "lastLogin")){ // last login not returned for users that never logged before
                Assert::type("string",$u->lastLogin);
                Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $u->lastLogin)); //timezone correction check
            }
            
            Assert::type("string",$u->status);
            Assert::true(in_array($u->status, ["PLAYER", "MEMBER", "SICK", "DELETED", "INIT"]));

            if(property_exists($u, "firstName")) Assert::type("string",$u->firstName);
            if(property_exists($u, "lastName")) Assert::type("string",$u->lastName);
            Assert::type("string",$u->callName);
            if(property_exists($u, "language")) Assert::type("string",$u->language);
            if(property_exists($u, "email")) Assert::type("string",$u->email);
            Assert::type("string",$u->jerseyNumber);
            if(property_exists($u, "gender")) Assert::type("string",$u->gender);
            if(property_exists($u, "street")) Assert::type("string",$u->street);
            if(property_exists($u, "city")) Assert::type("string",$u->city);
            if(property_exists($u, "zipCode")) Assert::type("string",$u->zipCode);
            if(property_exists($u, "phone")) Assert::type("string",$u->phone);
            if(property_exists($u, "phone2")) Assert::type("string",$u->phone2);
            if(property_exists($u, "birthDate")) Assert::type("string",$u->birthDate);

            Assert::type("int",$u->nameDayMonth);
            Assert::type("int",$u->nameDayDay);
            Assert::type("string",$u->pictureUrl);
            if(property_exists($u, "fullName")) Assert::type("string",$u->fullName);
            Assert::type("string",$u->displayName);
            Assert::type("string",$u->webName);
            Assert::type("int",$u->errCnt);
            Assert::type("array",$u->errFls);
            foreach ($u->errFls as $errF) {
                Assert::type("string",$errF);
            }
            
        }
    }
    
}

$test = new APIUsersTest($container);
$test->run();
