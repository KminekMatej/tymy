<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';
Tester\Environment::skip('Temporary skipping');
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class DiscussionPresenterTest extends TapiTestCase {

    const PRESENTERNAME = "Discussion";
    
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
        Assert::true($dom->has('div.container'));
        Assert::true($dom->has('ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        
        Assert::true($dom->has('div.container.discussions'));
        Assert::true(count($dom->find('div.container.discussions div.row')) >= 1);
    }
    
    
    function getDiscussionNames() {
        return $GLOBALS["testedTeam"]["testDiscussionName"];
    }

    /**
     *
     * @dataProvider getDiscussionNames
     */
    function testActionDiscussion($discussionName){
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'discussion', 'discussion' => $discussionName));
        $this->presenter->getUser()->setExpiration('2 minutes');
        $this->presenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $re = '/&(?!(?:apos|quot|[gl]t|amp);|#)/';

        $dom = NULL;
        $html = (string)$response->getSource();
        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace($re, "&amp;", $html);
        
        $dom = Tester\DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        
        //has breadcrumbs
        
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 3);
        
        Assert::equal(count($dom->find('div.container.my-2 div.row.justify-content-md-center')), 2);
        Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 textarea#addPost'));
        Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline input.form-control.mr-sm-2'));
        Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline input.form-control.btn.btn-outline-success.mr-auto'));
        Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline button.btn.btn-primary'));
        
        Assert::true($dom->has('div.container.discussion#snippet--discussion'));
        Assert::true(count($dom->find('div.container.discussion#snippet--discussion div.row'))<=20);
    }
}

$test = new DiscussionPresenterTest($container);
$test->run();
