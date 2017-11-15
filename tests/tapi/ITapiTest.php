<?php

namespace Test;
use Tester;
use Tester\Assert;

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
        Assert::truthy($object);
        Assert::type("\Nette\Reflection\ClassType", $object->getReflection());
        $tapiname = $object->getTapiName();
        Assert::type("string", $tapiname);
        if(in_array($tapiname, ["login","users/register", "pwdlost", "pwdreset"])){
            Assert::equal(FALSE, $object->getTSIDRequired());
        } else {
            Assert::equal(TRUE, $object->getTSIDRequired());
        }
        
        $tsid = "123456";
        $object->setTsid($tsid);
        Assert::equal($tsid, $object->getTsid());
        Assert::equal($tsid, $object->getUriParams()["TSID"]);
    }
    
    protected function resetParentTest($object){
        Assert::null($object->getUriParams());
        Assert::null($object->getTsid());
        Assert::null($object->getRecId());
        Assert::null($object->getFullUrl());
        Assert::null($object->getPostData());
        Assert::type("string", $object->getMethod());
        Assert::equal("GET", $object->getMethod());
        Assert::equal(TRUE, $object->getJsonEncoding());
    }
    
}
