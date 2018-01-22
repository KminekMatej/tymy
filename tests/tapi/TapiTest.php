<?php

namespace Test\Tapi;

use Tester\TestCase;
use Tester\Assert;

/**
 * Project: tymy_v2
 * Description of TapiTest
 *
 * @author kminekmatej created on 19.1.2018, 11:32:15
 */
abstract class TapiTest extends TestCase {
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    protected $loginObj;
    
    protected $tapi_config;
    
    /** @var \Tapi\TapiService */
    private $tapiService;
    
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    
    /** @var \App\Model\TestAuthenticator */
    protected $testAuthenticator;
    
    /** @var \Nette\Security\User */
    protected $user;
    
    /** @var \Tapi\TapiObject */
    protected $tapiObject;
    
    abstract function getMethod();
    
    abstract function getCacheable();
    
    abstract function getJSONEncoding();
    
    abstract function testPerformSuccess();
    
    /**
     * Function performs all the neccessary inputs (setters) on tapiObject, but does not call getData yet
     */
    abstract function setCorrectInputParams();
    
    function __construct(\Nette\DI\Container $container) {
        $this->container = $container;
        $this->supplier = $this->container->getByType('App\Model\Supplier');
        $this->user = $this->container->getByType('Nette\Security\User');
        $this->tapiService = $this->container->getByType('Tapi\TapiService');
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $tapi_config["root"] = $GLOBALS["testedTeam"]["root"];
        $this->tapi_config = $tapi_config;
        $this->supplier->setTapi_config($tapi_config);
        $this->tapiAuthenticator = new \App\Model\TapiAuthenticator($this->supplier);
        $this->testAuthenticator = new \App\Model\TestAuthenticator($this->supplier);
        
        $this->tapiObject = $this->getTapiObject();
        $this->tapiObject->setSupplier($this->supplier);
        $this->primaryTests();
    }
    
    protected function getTapiObject(){
        if (strpos(get_class($this), "Test") === FALSE) {
            Assert::fail("Wrong class name specification - word Test not found");
        }
        $className = str_replace("Test", "", get_class($this));
        return $this->container->getByType($className);
    }
    
    protected function authenticateTapi($username, $password){
        $this->tapiAuthenticator->setTapiService($this->tapiService);
        $this->user->setAuthenticator($this->tapiAuthenticator);
        $this->user->login($username, $password);
    }
    
    protected function authenticateTest($username, $password){
        $this->user->setAuthenticator($this->testAuthenticator);
        $this->user->login($username, $password);
    }
    
    protected function getPerformSuccessData(){
        $this->authenticateTapi($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        return $this->tapiObject->getData(TRUE);
    }
    
    public function primaryTests(){
        $this->objectExistsTest();
        $this->objectConstructedTest();
        $this->errorNotLoggedInTest();
    }
    
    public function objectExistsTest(){
        Assert::truthy(get_class($this->tapiObject));
    }
    
    public function objectConstructedTest(){
        Assert::equal(0, $this->tapiObject->getWarnings());
        Assert::count(0, $this->tapiObject->getRequestParameters());
        Assert::equal($this->getMethod(), $this->tapiObject->getMethod());
        Assert::equal($this->getCacheable(), $this->tapiObject->isCacheable());
        Assert::equal($this->getJSONEncoding(), $this->tapiObject->getJsonEncoding());
        Assert::null($this->tapiObject->getRequestData());
    }

    public function errorNotLoggedInTest(){
        $this->authenticateTest("TESTLOGIN", "TESTPASS");
        $this->setCorrectInputParams();
        Assert::exception(function(){$this->tapiObject->getData(TRUE);} , "\Tapi\Exception\APIException", "Login failed. Wrong username or password.");
    }
}
