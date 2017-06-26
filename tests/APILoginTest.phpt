<?php
/**
 * TEST: Test Login on TYMY api
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

class APILoginTest extends Tester\TestCase {

    private $container;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
    }
    
    function testLoginSuccess(){
        var_dump($GLOBALS["testedTeam"]);
        $loginObj = new \Tymy\Login();
        $loginObj->team($GLOBALS["testedTeam"]["team"])
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
        Assert::type("string", $loginObj->team);
        Assert::equal($loginObj->team, $GLOBALS["testedTeam"]["team"]);
        Assert::true(is_object($loginObj));
        Assert::type("string", $loginObj->result->status);
        Assert::equal($loginObj->result->status, "OK");
        Assert::type("string", $loginObj->result->sessionKey);
        Assert::true(is_object($loginObj->result->data));
        Assert::type("int", $loginObj->result->data->id);
        Assert::type("string", $loginObj->result->data->login);
        Assert::type("bool", $loginObj->result->data->canLogin);
        Assert::type("string", $loginObj->result->data->lastLogin);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $loginObj->result->data->lastLogin)); //timezone correction check
        Assert::type("string", $loginObj->result->data->status);
        Assert::true(in_array($loginObj->result->data->status, ["PLAYER","MEMBER","SICK"]));
        
        if (property_exists($loginObj->result->data, "roles")) {
            Assert::type("array", $loginObj->result->data->roles);
            foreach ($loginObj->result->data->roles as $role) {
                Assert::type("string", $role);
            }
        }


        Assert::type("string", $loginObj->result->data->oldPassword);
        Assert::type("string", $loginObj->result->data->firstName);
        Assert::type("string", $loginObj->result->data->lastName);
        Assert::type("string", $loginObj->result->data->callName);
        Assert::type("string", $loginObj->result->data->language);
        Assert::type("string", $loginObj->result->data->jerseyNumber);
        Assert::type("string", $loginObj->result->data->street);
        Assert::type("string", $loginObj->result->data->city);
        Assert::type("string", $loginObj->result->data->zipCode);
        Assert::type("string", $loginObj->result->data->phone);
        Assert::type("string", $loginObj->result->data->phone2);
        Assert::type("int", $loginObj->result->data->nameDayMonth);
        Assert::type("int", $loginObj->result->data->nameDayDay);
        Assert::type("string", $loginObj->result->data->pictureUrl);
        Assert::type("string", $loginObj->result->data->fullName);
        Assert::type("string", $loginObj->result->data->displayName);
    }
    
    /**
     * @throws Tymy\Exception\APIAuthenticationException
     */
    function testLoginFails(){
        $loginObj = new \Tymy\Login();
        $loginObj->team($GLOBALS["testedTeam"]["team"])
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword("sdfas6df84asd3c")
                ->fetch();
    }
    
}

$test = new APILoginTest($container);
$test->run();
