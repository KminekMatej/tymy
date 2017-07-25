<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class PollPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Poll";
    
    /** @var \Tymy\Polls */
    private $polls;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
        $this->polls = $this->container->getByType("\Tymy\Polls");
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
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
    }
    
    
    function allWebNames(){
        $polls = $this->polls->reset()
                ->getData();
        $inputArray = [];
        foreach ($polls as $p) {
            $inputArray[] = [$p];
        }
        return $inputArray;
    }
    
    /**
     * @dataProvider allWebNames
     */
    function testPoll($obj){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'poll', "anketa" => $obj->webName));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 3);
        
        //check showed items
        Assert::equal(count($dom->find('div.container.poll div.row div.col.my-3 div.card.sh-box div.card-block h4.card-title')), 1);
        Assert::equal(count($dom->find('div.container.poll div.row div.col.my-3 div.card.sh-box div.card-block h6.card-subtitle')), 1);
        
        Assert::true(count($dom->find('div.container.poll div.row div.col.my-3 div.card.sh-box div.card-block div.container div.row div.col-3.py-3.option')) > 0);
    }
}

$test = new PollPresenterTest($container);
$test->run();
