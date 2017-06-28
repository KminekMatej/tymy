<?php

namespace Test;
use Tester;

/**
 * APITymyTest - header class for all API tests
 *
 * @author kminekmatej
 */
class TapiTestCase extends Tester\TestCase {
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    protected $loginObj;
    protected $login;
    protected $tapi_config;
        
    protected function initTapiConfiguration($container){
        $this->supplier = $container->getByType('App\Model\Supplier');
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $this->tapi_config = $tapi_config;
        $this->supplier->setTapi_config($tapi_config);
    }
    
    public function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->setSupplier($this->supplier)
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }
    
}
