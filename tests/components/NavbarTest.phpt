<?php
/**
 * TEST: Test NavBar on presenters
 * 
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

class NavbarTest extends Tester\TestCase {

    private $container;
    private $presenter;
    
    /** @var \Tymy\Discussions */
    private $discussions;
    
    /** @var \Tymy\Polls */
    private $polls;
    
    /** @var \Tymy\Events */
    private $events;

    /** @var \Tymy\Users */
    private $users;

    /** @var \Nette\Security\User */
    protected $user;
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    /** @var \App\Model\TestAuthenticator */
    protected $testAuthenticator;
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
        $this->user = $this->container->getByType('Nette\Security\User');
        $this->supplier = $this->container->getByType('App\Model\Supplier');
        $this->discussions = $this->container->getByType('Tymy\Discussions');
        $this->polls = $this->container->getByType('Tymy\Polls');
        $this->events = $this->container->getByType('Tymy\Events');
        $this->users = $this->container->getByType('Tymy\Users');
        
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $tapi_config["root"] = $GLOBALS["testedTeam"]["root"];
        
        $this->supplier->setTapi_config($tapi_config);
        $this->tapiAuthenticator = new \App\Model\TapiAuthenticator($this->supplier);
        $this->testAuthenticator = new \App\Model\TestAuthenticator($this->supplier);
    }
    
    protected function userTapiAuthenticate($username, $password){
        $this->user->setAuthenticator($this->tapiAuthenticator);
        $this->user->login($username, $password);
    }
    
    protected function userTestAuthenticate($username, $password){
        $this->user->setAuthenticator($this->testAuthenticator);
        $this->user->login($username, $password);
    }
    
    function mockPresenter($presenter){
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->presenter = $presenterFactory->createPresenter($presenter);
        $this->presenter->autoCanonicalize = FALSE;
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
    }
    
    function getHomepageHtml($presenter){
        $request = new Nette\Application\Request($presenter, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);
        return $response->getSource();
    }
    
    
    function getPresenters(){
        return [["Homepage"],["Discussion"],["Event"],["Team"],["Poll"]]; 
    }
    
    /**
     * Test supposed to be run for all presenters
     * 
     * @dataProvider getPresenters
     */
    function testNavbarComponents($presenterMock){
        $this->mockPresenter($presenterMock);
        $html = (string) $this->getHomepageHtml($presenterMock);
        
        $dObj = $this->discussions->reset()->getData();
        $pObj = $this->polls->reset()->getData();
        $uObj = $this->users->reset()->getResult();
        $eObj = $this->events->reset()
                ->setWithMyAttendance(true)
                ->setFrom(date("Ymd"))
                ->setTo(date("Ymd", strtotime(" + 1 month")))
                ->getData();

        $dom = Tester\DomQuery::fromHtml($html);
        Assert::true($dom->has('div#snippet-navbar-nav'));
        Assert::true($dom->has('nav.navbar.navbar-expand-lg.navbar-dark.bg-dark.fixed-top'));
        Assert::true($dom->has('button.navbar-toggler'));
        Assert::true($dom->has('span.navbar-toggler-icon'));
        Assert::true($dom->has("a.navbar-brand[href]"));
        
        Assert::true($dom->has("ul.navbar-nav.mr-auto"));
        Assert::equal(count($dom->find("ul.navbar-nav")), 2); //2 navbar menus (left and right)
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item")), 5); //5 menu items in the first menu
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")), 5); //5 of them with dropdown
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[0]->div->a), count((array)$dObj)); //check if the discussions are all displayed
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[1]->div->a), count((array)$eObj) + 1, "Displayed " . count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[1]->div->a) . "events instead of expected " . count((array)$eObj) + 1); //check display all events + 1
        $caption = "Inits " . $uObj->counts["INIT"];
        $teamMenuDropdownCount = ($uObj->counts["INIT"] > 0 && $this->user->isAllowed('users','canSeeRegisteredUsers')) ? 6 : 5;
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[2]->div->a), $teamMenuDropdownCount, $caption);
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[3]->div->a), count((array)$pObj)); //check if the polls are all displayed
        
        $settingsMenuDropdownCount = 0;
        if($this->user->isAllowed('settings','discussions')) $settingsMenuDropdownCount++;
        if($this->user->isAllowed('settings','events')) $settingsMenuDropdownCount++;
        if($this->user->isAllowed('settings','team')) $settingsMenuDropdownCount++;
        if($this->user->isAllowed('settings','polls')) $settingsMenuDropdownCount++;
        if($this->user->isAllowed('settings','reports')) $settingsMenuDropdownCount++;
        if($this->user->isAllowed('settings','permissions')) $settingsMenuDropdownCount++;
        if($this->user->isAllowed('settings','app')) $settingsMenuDropdownCount++;
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[4]->div->a), $settingsMenuDropdownCount); //check if the settings are all displayed
        
        Assert::equal(count($dom->find("ul.navbar-nav li.nav-item.dropdown")[5]->div->a), 1); //check if the right menu is displayed
    }
    
}

$test = new NavbarTest($container);
$test->run();
