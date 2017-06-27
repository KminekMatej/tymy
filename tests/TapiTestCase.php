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
    
    function setUp() {
        parent::setUp();
        $this->tapi_config = (array)$GLOBALS["testedTeam"]["tapi_config"];
        $this->supplier = new \App\Model\Supplier($this->tapi_config);
    }
    
    public function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->setSupplier($this->supplier)
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }
    
}
