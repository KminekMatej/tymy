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
        Assert::exception(function(){$this->users->reset()->getResult(TRUE);} , "Nette\Security\AuthenticationException", "Login failed.");
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
            if(property_exists($u, "lastLogin")){ // last login not returned for users that never logged before
                Assert::type("string",$u->lastLogin);
                Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $u->lastLogin)); //timezone correction check
            }
            
            Assert::type("string",$u->status);
            Assert::true(in_array($u->status, ["PLAYER", "MEMBER", "SICK", "DELETED", "INIT"]));

            Assert::type("string",$u->firstName);
            Assert::type("string",$u->lastName);
            Assert::type("string",$u->callName);
            Assert::type("string",$u->language);
            //Assert::type("string",$u->email);
            Assert::type("string",$u->jerseyNumber);
            //Assert::type("string",$u->gender);
            //Assert::type("string",$u->street);
            //Assert::type("string",$u->city);
            //Assert::type("string",$u->zipCode);
            //Assert::type("string",$u->phone);
            //Assert::type("string",$u->phone2);
            //Assert::type("string",$u->birthDate);
            Assert::type("int",$u->nameDayMonth);
            Assert::type("int",$u->nameDayDay);
            Assert::type("string",$u->pictureUrl);
            Assert::type("string",$u->fullName);
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
