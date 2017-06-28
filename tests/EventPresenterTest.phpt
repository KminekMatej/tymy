<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class EventPresenterTest extends TapiTestCase {

    const PRESENTERNAME = "Event";
    
    private $container;
    private $presenter;
    

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        $this->initTapiConfiguration($this->container);
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->presenter = $presenterFactory->createPresenter(self::PRESENTERNAME);
        $this->presenter->autoCanonicalize = FALSE;
    }
    
    function testSignInFailsRedirect(){
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::equal('http:///sign/in', substr($response->getUrl(), 0, 15));
        Assert::equal(302, $response->getCode());
    }
    
    function testActionDefault(){
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'default'));
        $this->presenter->getUser()->setExpiration('2 minutes');
        $this->presenter->getUser()->setAuthenticator($this->tapiAuthenticator);
        $this->presenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        
        Assert::true($dom->has('div.container.events'));
        Assert::true(count($dom->find('div.container.events div.row')) >= 1);
        Assert::true($dom->has('div.container.events div.row div.col-md-7.my-3 div.card.sh-box#calendar'));
        
        Assert::true($dom->has('div.container.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events'));
        Assert::equal(count($dom->find('div.container.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events div.card.sh-box.agenda[data-month]')), 13);
    }
}

$test = new EventPresenterTest($container);
$test->run();
