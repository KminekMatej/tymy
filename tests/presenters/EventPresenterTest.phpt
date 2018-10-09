<?php

namespace Test;

use Nette;
use Tester\Assert;
use Environment;
use Tester\DomQuery;


$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class EventPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Event";
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testActionDefault(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        
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
    
    function eventDetailInputs() {
        return [
            [
                [
                    "adminMode" => TRUE,
                    "username" => $GLOBALS["testedTeam"]["user_admin"],
                    "password" => $GLOBALS["testedTeam"]["pass_admin"],
                    "eventId" => $GLOBALS["testedTeam"]["testEventId"]
                ]
            ],
            [
                [
                    "adminMode" => FALSE,
                    "username" => $GLOBALS["testedTeam"]["user"],
                    "password" => $GLOBALS["testedTeam"]["pass"],
                    "eventId" => $GLOBALS["testedTeam"]["testEventId"]
                ]
            ],
        ];
    }

    /** @dataProvider eventDetailInputs */
    function testActionEventAdmin($inputs){
        $this->userTapiAuthenticate($inputs["username"], $inputs["password"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'event', "udalost" => $inputs["eventId"]));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link
        
        //test body
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-header div.row div.col h4.card-title'));
        $pencilBtn = 'div.container.event div.row div.col div.card.sh-box.my-3 div.card-header div.row div.col.col-md-auto a.btn.btn-sm.btn-light.btn-light-bordered i.fa.fa-edit';
        if($inputs["adminMode"]){
            Assert::true($dom->has($pencilBtn));
        } else {
            Assert::false($dom->has($pencilBtn));
        }
        
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body h6.card-subtitle.mb-2.text-muted span a'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body p.card-text'));
        Assert::equal(count($dom->find('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-md-4 table.table.mb-0 tr th')), 3);
        Assert::equal(count($dom->find('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-md-4 table.table.mb-0 tr td')), 3);
        
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-md-8.d-flex.flex-column-reverse.align-items-center input.form-control.form-control-sm.custom-btn-sm'));
        Assert::count(3, $dom->find('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-md-8.d-flex.flex-column-reverse.align-items-center button.btn.custom-btn-sm'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-md-8.d-flex.flex-column-reverse.align-items-center div#snippet--attendanceWarning'));
        
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3#snippet--attendanceTabs div.card-header ul.nav.nav-tabs.flex-column.flex-sm-row.card-header-tabs li.nav-item'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3#snippet--attendanceTabs div.card-body div.tab-content div.tab-pane.fade.player-list div.row.my-2'));
    }
}

$test = new EventPresenterTest($container);
$test->run();
