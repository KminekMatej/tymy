<?php

namespace Test;
use Tester;

/**
 * APITymyTest - header class for all API tests
 *
 * @author kminekmatej
 */
class ITapiTest extends Tester\TestCase {
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    protected $loginObj;
    protected $login;
    
    protected $tapi_config;
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    /** @var \App\Model\TestAuthenticator */
    protected $testAuthenticator;
    
    public function __construct(\App\Model\Supplier $supplier) {
        $this->supplier = $supplier;
    }
    
    protected function setUp(){
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $this->tapi_config = $tapi_config;
        
        $this->supplier->setTapi_config($tapi_config);
        $this->tapiAuthenticator = new \App\Model\TapiAuthenticator($this->supplier);
        $this->testAuthenticator = new \App\Model\TestAuthenticator();
    }
    
    public function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->setSupplier($this->supplier)
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }
    
}
