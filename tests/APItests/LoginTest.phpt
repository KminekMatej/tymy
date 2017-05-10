<?php
/**
 * TEST: Test Login on TYMY api
 * 
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class LoginTest extends Tester\TestCase {

    private $container;
    private $presenter;
    private $identity;
    private $admin;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->presenter = $presenterFactory->createPresenter('Base');
        $this->presenter->autoCanonicalize = FALSE;
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testLogin(){
        $loginObj = new \Tymy\Login($this->presenter);
        
        
        $this->mockPresenter($presenterMock);
        $html = (string) $this->getHomepageHtml();
        $discussions = new \Tymy\Discussions($this->presenter);
        $dObj = $discussions->fetch();
        
        $dom = Tester\DomQuery::fromHtml($html);
        Assert::true($dom->has('div#snippet-navbar-nav'));
        Assert::true($dom->has('nav.navbar.navbar-inverse.navbar-toggleable-md.bg-inverse.fixed-top'));
        Assert::true($dom->has('button.navbar-toggler.navbar-toggler-right'));
        Assert::true($dom->has('span.navbar-toggler-icon'));
        Assert::true($dom->has("a.navbar-brand[href]"));
        
        Assert::true($dom->has("ul.navbar-nav.mr-auto"));
        Assert::true($dom->has("ul.navbar-nav.mr-auto"));
        
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item")), 3); //3 menu items
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")), 2); //2 of them with dropdown
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[0]->div->a), count((array)$dObj)); //check if the discussions are all displayed
        Assert::equal(count($dom->find("ul.navbar-nav.mr-auto li.nav-item.dropdown")[1]->div->a), 5); //there are 5 menu items on second dropdown (team)
        
        Assert::equal(count($dom->find("ul.navbar-nav")), 2); //there are two nav menus, left and right
        $logoutBtn = (array)$dom->find("ul.navbar-nav")[1]->li->a;
        
        Assert::equal($logoutBtn[0], "Odhlásit");
    }
    
}

$test = new NavbarTest($container);
$test->run();
