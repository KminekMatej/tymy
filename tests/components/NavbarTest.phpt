<?php
/**
 * TEST: Test NavBar on presenters
 * 
 * 
 */

namespace Test;

use Nette;
use Tester\Assert;
use Tester\TestCase;
use Tester\Environment;
use Tester\DomQuery;
use Tapi\EventListResource;
use Tapi\PollListResource;
use Tapi\NoteListResource;
use Tapi\TapiService;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

Environment::lock('tapi', __DIR__ . '/../lockdir'); //belong to the group of tests which should not run paralelly
        
class NavbarTest extends TestCase {

    private $container;
    private $presenter;
    
    /** @var DiscussionListResource */
    private $discussionList;
    
    /** @var PollListResource */
    private $pollList;
    
    /** @var NoteListResource */
    private $noteList;
    
    /** @var EventListResource */
    private $eventList;

    /** @var UserListResource */
    private $userList;

    /** @var \Nette\Security\User */
    protected $user;
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    /** @var \App\Model\TestAuthenticator */
    protected $testAuthenticator;
    
    /** @var TapiService */
    protected $tapiService;
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
        $this->user = $this->container->getByType('Nette\Security\User');
        $this->supplier = $this->container->getByType('App\Model\Supplier');
        $this->discussionList = $this->container->getByType('Tapi\DiscussionListResource');
        $this->pollList = $this->container->getByType('Tapi\PollListResource');
        $this->noteList = $this->container->getByType('Tapi\NoteListResource');
        $this->eventList = $this->container->getByType('Tapi\EventListResource');
        $this->userList = $this->container->getByType('Tapi\UserListResource');
        $this->tapiService = $this->container->getByType('Tapi\TapiService');
        
        $tapi_config = $this->supplier->getTapi_config();
        $tapi_config["tym"] = $GLOBALS["testedTeam"]["team"];
        $tapi_config["root"] = $GLOBALS["testedTeam"]["root"];
        
        $this->supplier->setTapi_config($tapi_config);
        $this->testAuthenticator = new \App\Model\TestAuthenticator($this->supplier);
    }
    
    protected function userTapiAuthenticate($username, $password){
        $this->user->login($username, $password);
    }
    
    protected function userTestAuthenticate($username, $password){
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
        
        $dObj = $this->discussionList->getData();
        $pObj = $this->pollList->getData();
        $nObj = $this->noteList->getData();
        $uObj = $this->userList->getData();
        $uCounts = $this->userList->getCounts();
        $eObj = $this->eventList
                ->setFrom(date("Ymd"))
                ->setTo(date("Ymd", strtotime(" + 1 month")))
                ->setOrder("startTime")
                ->getData();
        $dom = DomQuery::fromHtml($html);
        Assert::true($dom->has('div#snippet-navbar-nav'));
        Assert::true($dom->has('nav.navbar.navbar-expand-lg.navbar-dark.bg-dark.fixed-top'));
        Assert::true($dom->has('button.navbar-toggler'));
        Assert::true($dom->has('span.navbar-toggler-icon'));
        Assert::true($dom->has("a.navbar-brand[href]"));
        
        Assert::true($dom->has("ul.navbar-nav.mr-auto"));
        Assert::equal(count($dom->find("ul.navbar-nav")), 2); //2 navbar menus (left and right)
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item")), 6); //5 menu items in the first menu
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")), 6); //5 of them with dropdown
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown[name='discussions'] div a")), count((array)$dObj)); //check if the discussions are all displayed
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown[name='events'] div a")), count((array)$eObj) + 1, "Displayed " . count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[1]->div->a) . "events instead of expected " . count((array)$eObj) + 1); //check display all events + 1
        $caption = "Inits " . $uCounts["INIT"];
        
        $teamMenuDropdownCount = ($uCounts["INIT"] > 0 && $this->user->isAllowed('users','canSeeRegisteredUsers')) ? 7 : 6;
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown[name='team'] div a")), $teamMenuDropdownCount, $caption);
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown[name='polls'] div a")), count((array)$pObj)); //check if the polls are all displayed
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown[name='notes'] div a")), count((array)$nObj)); //check if the polls are all displayed
        $settingsDropdownCount = count($this->presenter->getAccessibleSettings());
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown[name='settings'] div a")), $settingsDropdownCount); //7 settings items are in menu
        
        Assert::true(count($dom->find("ul.navbar-nav li.nav-item.dropdown")[6]->div->a)>1); //check if the right menu is displayed
    }
    
}

$test = new NavbarTest($container);
$test->run();
