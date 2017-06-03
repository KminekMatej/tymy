<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class HomepagePresenterTest extends Tester\TestCase {

    private $username = "autotest";
    private $pass = "123456.";
    
    private $container;
    private $presenter;
    private $admin;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->presenter = $presenterFactory->createPresenter('Homepage');
        $this->presenter->autoCanonicalize = FALSE;
    }
    
    function testSignInFailsRedirect(){
        $request = new Nette\Application\Request('Homepage', 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::equal('http:///sign/in', substr($response->getUrl(), 0, 15));
        Assert::equal(302, $response->getCode());
    }
    
    function testHomepageComponents(){
        $request = new Nette\Application\Request('Homepage', 'GET', array('action' => 'default'));
        $this->presenter->getUser()->setExpiration('2 minutes');
        $this->presenter->getUser()->login($this->username, $this->pass);
        Assert::equal($this->presenter->getUser()->getIdentity()->data["data"]->callName, "AUTOTEST");
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container'));
        Assert::true($dom->has('ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
    }
}

$test = new HomepagePresenterTest($container);
$test->run();
