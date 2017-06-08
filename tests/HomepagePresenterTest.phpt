<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class HomepagePresenterTest extends Tester\TestCase {

    const PRESENTERNAME = "Homepage";
    
    private $container;
    private $presenter;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
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
        $this->presenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        
        Assert::true($dom->has('div.container.homepage'));
        Assert::true(count($dom->find('div.container.homepage div.row')) >= 2); // at least two rows, two makes the main layout, more rows are inside for discussions
        Assert::true($dom->has('div.container.homepage div.row div.col-md-5.my-3 div.card.sh-box#calendar'));
        Assert::true($dom->has('div.container.homepage div.row div.col-md-5.my-3 a.btn.btn-sm.btn-secondary.d-block'));
        
        Assert::true($dom->has('div.container.homepage div.row div.col-md-7.my-3 div.card.sh-box div.card-header h4.card-title'));
        Assert::true($dom->has('div.container.homepage div.row div.col-md-7.my-3 div.card.sh-box div.card-block'));
        
    }
}

$test = new HomepagePresenterTest($container);
$test->run();
