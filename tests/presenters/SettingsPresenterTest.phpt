<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

Tester\Environment::skip('Test skipped - no finished yet.');

class SettingsPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Settings";
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testActionDefault(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
        
        Assert::true($dom->has('div.container.events'));
        Assert::true(count($dom->find('div.container.events div.row')) >= 1);
        Assert::true($dom->has('div.container.events div.row div.col-md-7.my-3 div.card.sh-box#calendar'));
        
        Assert::true($dom->has('div.container.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events-agenda'));
        Assert::equal(count($dom->find('div.container.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events-agenda div.card.sh-box.agenda[data-month]')), 13);
    }
    
    function testActionEvent() {
        Assert::true(FALSE); //TODO
    }

    function testActionDiscussions() {
        Assert::true(FALSE); //TODO
    }

    function testActionEvents() {
        Assert::true(FALSE); //TODO
    }

    function testActionPolls() {
        Assert::true(FALSE); //TODO
    }

    function testActionReports() {
        Assert::true(FALSE); //TODO
    }

    function testActionPermissions() {
        Assert::true(FALSE); //TODO
    }

    function testActionApp() {
        Assert::true(FALSE); //TODO
    }

    function testActionDiscussion() {
        Assert::true(FALSE); //TODO
    }

    function testActionEvent_new() {
        Assert::true(FALSE); //TODO
    }

}

$test = new SettingsPresenterTest($container);
$test->run();
