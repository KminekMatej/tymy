<?php

namespace Test;

use Nette\Application\Request;
use Tester\Assert;

/**
 *
 * @author kminekmatej
 */
abstract class IPresenterTest extends \Tester\TestCase {
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    /** @var \App\Model\TestAuthenticator */
    protected $testAuthenticator;
    /** @var \Nette\Security\User */
    protected $user;
    
    /** @var \App\Presenters\SecuredPresenter */
    protected $presenter;
    
    /** @var \Nette\DI\Container */
    protected $container;
    
    /** @var \Nette\Application\IPresenterFactory */
    protected $presenterFactory;
    
    protected function setUp() {
        $this->supplier = $this->container->getByType('App\Model\Supplier');
        $this->user = $this->container->getByType('Nette\Security\User');
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $tapi_config["root"] = $GLOBALS["testedTeam"]["root"];
        
        $this->supplier->setTapi_config($tapi_config);
        $this->tapiAuthenticator = new \App\Model\TapiAuthenticator($this->supplier);
        $this->testAuthenticator = new \App\Model\TestAuthenticator($this->supplier);
        
        $this->presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        if($this->getPresenterName() != "undefined"){
            $this->presenter = $this->presenterFactory->createPresenter($this->getPresenterName());
            $this->presenter->autoCanonicalize = FALSE;
        }
        

        return parent::setUp();
    }

    protected function tearDown() {
        return parent::tearDown();
    }
    
    protected function getPresenterName(){
        $class = get_called_class();
        return $class::PRESENTERNAME;
    }
    
    protected function userTapiAuthenticate($username, $password){
        $this->user->setAuthenticator($this->tapiAuthenticator);
        $this->user->login($username, $password);
    }
    
    protected function userTestAuthenticate($username, $password){
        $this->user->setAuthenticator($this->testAuthenticator);
        $this->user->login($username, $password);
    }
    
    function testSignInFailsRedirects(){
        if(in_array($this->getPresenterName(), ["Sign","undefined"])) return;
        $this->user->logout();
        $request = new Request($this->getPresenterName(), 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::equal('http:///sign/in', substr($response->getUrl(), 0, 15));
        Assert::equal(302, $response->getCode());
    }

    
}
