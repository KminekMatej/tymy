<?php
/**
 * TEST: Test NavBar on Homepage presenter
 * 
 * 
 */



namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class NavbarTest extends Tester\TestCase {

    private $container;
    private $presenter;
    private $identity;
    private $admin;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        
    }
    
    function mockPresenter($presenter){
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->presenter = $presenterFactory->createPresenter($presenter);
        $this->presenter->autoCanonicalize = FALSE;
        $this->presenter->getUser()->setExpiration('2 minutes');
        $this->presenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);
        $this->presenter->getUser()->getIdentity()->tym = "dev";
    }
    
    function getHomepageHtml(){
        $request = new Nette\Application\Request('Homepage', 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);
        return $response->getSource();
    }
    
    
    function getPresenters(){
        return [["Homepage"],["Discussion"],["Event"],["Team"]]; 
    }
    
    /**
     * Test supposed to be run for all presenters
     * 
     * @dataProvider getPresenters
     */
    function testNavbarComponents($presenterMock){
        $this->mockPresenter($presenterMock);
        $html = (string) $this->getHomepageHtml();
        $discussions = new \Tymy\Discussions($this->presenter);
        
        $dObj = $discussions->fetch();
        
        $polls = new \Tymy\Polls($this->presenter);
        $pObj = $polls->fetch();
        
        $events = new \Tymy\Events($this->presenter);
        $eObj = $events
                ->withMyAttendance(true)
                ->from(date("Ymd"))
                ->to(date("Ymd", strtotime(" + 14 days")))
                ->fetch();

        $dom = Tester\DomQuery::fromHtml($html);
        Assert::true($dom->has('div#snippet-navbar-nav'));
        Assert::true($dom->has('nav.navbar.navbar-inverse.navbar-toggleable-md.bg-inverse.fixed-top'));
        Assert::true($dom->has('button.navbar-toggler.navbar-toggler-right'));
        Assert::true($dom->has('span.navbar-toggler-icon'));
        Assert::true($dom->has("a.navbar-brand[href]"));
        
        Assert::true($dom->has("ul.navbar-nav.mr-auto"));
        Assert::true($dom->has("ul.navbar-nav.mr-auto"));
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item")), 4); //4 menu items
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")), 4); //4 of them with dropdown
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[0]->div->a), count((array)$dObj)); //check if the discussions are all displayed
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[1]->div->a), count((array)$eObj)); //check if the events are all displayed
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[2]->div->a), 5); //there are 5 menu items on second dropdown (team)
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[3]->div->a), count((array)$pObj)); //check if the polls are all displayed
        
        Assert::equal(count($dom->find("ul.navbar-nav")), 2); //there are two nav menus, left and right
        $logoutBtn = (array)$dom->find("ul.navbar-nav")[1]->li->a;
        
        Assert::equal($logoutBtn[0], "Odhlásit");
    }
    
}

$test = new NavbarTest($container);
$test->run();
