<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class AllPresentersTest extends IPresenterTest {

    const PRESENTERNAME = "undefined";
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    function testHopsThroughPresenters(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $hopList = [
            ["presenter" => "Homepage", "action" => "default", "params" => []],
            ["presenter" => "Discussion", "action" => "default", "params" => []],
            ["presenter" => "Discussion", "action" => "discussion", "params" => ["discussion" => 2, "page" => 1, "search" => ""]],
            ["presenter" => "Discussion", "action" => "discussion", "params" => ["discussion" => 2, "page" => 2, "search" => ""]],
            ["presenter" => "Event", "action" => "default", "params" => []],
            ["presenter" => "Event", "action" => "event", "params" => ["udalost" => "119-trenink"]],
            ["presenter" => "Team", "action" => "default", "params" => []],
            ["presenter" => "Team", "action" => "players", "params" => []],
            ["presenter" => "Team", "action" => "members", "params" => []],
            ["presenter" => "Team", "action" => "players", "params" => []],
            ["presenter" => "Team", "action" => "sicks", "params" => []],
            ["presenter" => "Team", "action" => "inits", "params" => []],
            ["presenter" => "Team", "action" => "player", "params" => ["player" => "11-matejskej-kminek"]],
            ["presenter" => "Poll", "action" => "default", "params" => []],
            ["presenter" => "Poll", "action" => "poll", "params" => ["anketa" => "2-nejaka-anketa"]],
            ["presenter" => "Poll", "action" => "poll", "params" => ["anketa" => "1-klasika"]],
        ];
        
        shuffle($hopList); // shuffle array each test
        
        foreach ($hopList as $hop) {
            $this->presenter = $this->presenterFactory->createPresenter($hop["presenter"]);
            $this->presenter->autoCanonicalize = FALSE;
            $request = new Nette\Application\Request($hop["presenter"], 'GET', array_merge(['action' => $hop["action"]], $hop["params"]));
            $response = $this->presenter->run($request);

            Assert::type('Nette\Application\Responses\TextResponse', $response);

            $html = (string) $response->getSource();
            $dom = Tester\DomQuery::fromHtml($html);

            //has navbar
            Assert::true($dom->has('div#snippet-navbar-nav'));
            //has breadcrumbs
            Assert::true($dom->has('div.container'));
            Assert::true($dom->has('ol.breadcrumb'));
        }
    }
}

$test = new AllPresentersTest($container);
$test->run();
