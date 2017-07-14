<?php
/**
 * TEST: Test Login on TYMY api
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

class APILoginTest extends ITapiTest {

    /** @var \Tymy\Login */
    private $login;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->login;
    }
    
    protected function setUp() {
        $this->login = new \Tymy\Login(new \App\Model\Supplier($this->tapi_config));
        parent::setUp();
        $this->login->setSupplier($this->supplier);
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    
    function testLoginSuccess(){
        $this->login->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
        
        Assert::true(is_object($this->login));
        Assert::type("string", $this->login->result->status);
        Assert::equal($this->login->result->status, "OK");
        Assert::type("string", $this->login->result->sessionKey);
        Assert::true(is_object($this->login->result->data));
        Assert::type("int", $this->login->result->data->id);
        Assert::type("string", $this->login->result->data->login);
        Assert::type("bool", $this->login->result->data->canLogin);
        Assert::type("string", $this->login->result->data->lastLogin);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $this->login->result->data->lastLogin)); //timezone correction check
        Assert::type("string", $this->login->result->data->status);
        Assert::true(in_array($this->login->result->data->status, ["PLAYER","MEMBER","SICK"]));
        
        if (property_exists($this->login->result->data, "roles")) {
            Assert::type("array", $this->login->result->data->roles);
            foreach ($this->login->result->data->roles as $role) {
                Assert::type("string", $role);
            }
        }


        Assert::type("string", $this->login->result->data->oldPassword);
        Assert::type("string", $this->login->result->data->firstName);
        Assert::type("string", $this->login->result->data->lastName);
        Assert::type("string", $this->login->result->data->callName);
        Assert::type("string", $this->login->result->data->language);
        Assert::type("string", $this->login->result->data->jerseyNumber);
        Assert::type("string", $this->login->result->data->street);
        Assert::type("string", $this->login->result->data->city);
        Assert::type("string", $this->login->result->data->zipCode);
        Assert::type("string", $this->login->result->data->phone);
        Assert::type("string", $this->login->result->data->phone2);
        Assert::type("int", $this->login->result->data->nameDayMonth);
        Assert::type("int", $this->login->result->data->nameDayDay);
        Assert::type("string", $this->login->result->data->pictureUrl);
        Assert::type("string", $this->login->result->data->fullName);
        Assert::type("string", $this->login->result->data->displayName);
    }
    
    /**
     * @throws Tymy\Exception\APIAuthenticationException
     */
    function testLoginFails(){
        $this->login->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword("sdfas6df84asd3c")
                ->fetch();
    }
    
}

$test = new APILoginTest($container);
$test->run();
