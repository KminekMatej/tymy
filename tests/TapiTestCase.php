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
    
    function setUp() {
        parent::setUp();
        $this->supplier = new \App\Model\Supplier($GLOBALS["testedTeam"]["team"]);
    }
    
    public function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->setSupplier($this->supplier)
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }
    
}
