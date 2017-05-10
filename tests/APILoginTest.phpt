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

class APILoginTest extends Tester\TestCase {

    private $container;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testLoginSuccess(){
        $loginObj = new \Tymy\Login();
        $loginObj->team("dev")
                ->setUsername($GLOBALS["username"])
                ->setPassword($GLOBALS["password"])
                ->fetch();
        Assert::type("string", $loginObj->team);
        Assert::equal($loginObj->team, "dev");
        Assert::true(is_object($loginObj));
        Assert::type("string", $loginObj->result->status);
        Assert::equal($loginObj->result->status, "OK");
    }
    
    /**
     * @throws Tymy\Exception\APIAuthenticationException
     */
    function testLoginFails(){
        $loginObj = new \Tymy\Login();
        $loginObj->team("dev")
                ->setUsername($GLOBALS["username"])
                ->setPassword("sdfas6df84asd3c")
                ->fetch();
    }
    
}

$test = new APILoginTest($container);
$test->run();
