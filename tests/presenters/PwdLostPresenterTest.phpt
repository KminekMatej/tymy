<?php

namespace Test;

use Nette;
use Tester\Assert;
use Tester\Environment;
use Tester\DomQuery;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class PwdLostPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Sign";
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testPwdLostComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'pwdlost'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = DomQuery::fromHtml($html);

        Assert::true($dom->has('input[name="email"]'));
        Assert::true($dom->has('input[name="send"]'));
        
        Assert::true($dom->has('a[href="/sign/in"]'));
        Assert::true($dom->has('a[href="/sign/pwdreset"]'));
    }
    
    function testPwdResetComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'pwdreset'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = DomQuery::fromHtml($html);

        Assert::true($dom->has('input[name="code"]'));
        Assert::true($dom->has('input[name="send"]'));
        
        Assert::true($dom->has('a[href="/sign/in"]'));
    }
    
    function testPwdNewComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'pwdnew', 'pwd' => 'newpass123'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = DomQuery::fromHtml($html);

        Assert::true($dom->has('input#pwd'));
        Assert::true($dom->has('a[href="/sign/in"]'));
    }
    
}

$test = new PwdLostPresenterTest($container);
$test->run();
