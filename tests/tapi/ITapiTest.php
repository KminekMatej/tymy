<?php

namespace Test;
use Tester;

/**
 * APITymyTest - header class for all API tests
 *
 * @author kminekmatej
 */
abstract class ITapiTest extends Tester\TestCase {
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    protected $loginObj;
    
    protected $tapi_config;
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    /** @var \App\Model\TestAuthenticator */
    protected $testAuthenticator;
    /** @var \Nette\Security\User */
    protected $user;
    
    abstract function getTestedObject();
    
    protected function setUp(){
        $this->supplier = $this->container->getByType('App\Model\Supplier');
        $this->user = $this->container->getByType('Nette\Security\User');
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $tapi_config["root"] = $GLOBALS["testedTeam"]["root"];
        $this->tapi_config = $tapi_config;
        
        $this->supplier->setTapi_config($tapi_config);
        $this->tapiAuthenticator = new \App\Model\TapiAuthenticator($this->supplier);
        $this->testAuthenticator = new \App\Model\TestAuthenticator($this->supplier);
        $tested_object = $this->getTestedObject();
        $this->objectPreTests($tested_object);
        $tested_object->setSupplier($this->supplier);
    }
    
    protected function userTapiAuthenticate($username, $password){
        $this->user->setAuthenticator($this->tapiAuthenticator);
        $this->user->login($username, $password);
    }
    
    protected function userTestAuthenticate($username, $password){
        $this->user->setAuthenticator($this->testAuthenticator);
        $this->user->login($username, $password);
    }
    
    protected function objectPreTests($object){
        Tester\Assert::truthy($object);
        Tester\Assert::type("\Nette\Reflection\ClassType", $object->getReflection());
        $tapiname = $object->getTapiName();
        Tester\Assert::type("string", $tapiname);
        if($tapiname == "login"){
            Tester\Assert::equal(FALSE, $object->getTSIDRequired());
        } else {
            Tester\Assert::equal(TRUE, $object->getTSIDRequired());
        }
        
        $tsid = "123456";
        $object->setTsid($tsid);
        Tester\Assert::equal($tsid, $object->getTsid());
        Tester\Assert::equal($tsid, $object->getUriParams()["TSID"]);
    }
    
}
