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
        $dom = DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
        
        $settingsDropdownCount = count($this->presenter->getAccessibleSettings());
        Assert::true($dom->has('div.container.settings'));
        Assert::equal(count($dom->find('div.container.settings div.row.my-4.justify-content-center div.col-md-4 div.card.sh-box.my-4.text-center a[href]')), $settingsDropdownCount);
    }
    
    function getListActions(){
        return [
            ["discussions"],
            ["events"],
            ["polls"]
        ];
    }
    
    /** @dataProvider getListActions */
    function testActionList($action) {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => $action));
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
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-header h4'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive'));
        
        Assert::true(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr[data-binder-id]'))>0);
    }
    
    function getFormActions(){
        return [
            [["action" => "discussions", "discussion" => "testovaci-diskuze"]],
            [["action" => "discussions", "discussion" => "tymova-diskuze"]],
        ];
    }
    
    /** @dataProvider getFormActions */
    function testActionForm($requestParams) {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', $requestParams);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
    }
    
    /** @dataProvider getFormActions */
    function testActionFormAdmin($requestParams) {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', $requestParams);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 3);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 4); //last item aint link
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-header h4'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table'));
        if($this->user->isAllowed('discussion','setup')){
            Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-footer.text-right button.btn.btn-danger.mx-2.binder-delete-btn i.fa.fa-times'), "Chyba v " . $requestParams["discussion"]);
            Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-footer.text-right button.btn.btn-lg.btn-primary.binder-save-btn i.fa.fa-save'));
        } else {
            Assert::true(!$dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-footer.text-right button.btn.btn-danger.mx-2.binder-delete-btn i.fa.fa-times'), "Chyba v " . $requestParams["discussion"]);
            Assert::true(!$dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-footer.text-right button.btn.btn-lg.btn-primary.binder-save-btn i.fa.fa-save'));
        }
    }

    function testActionApp() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => "app"));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $re = '/&(?!(?:apos|quot|[gl]t|amp);|#)/';

        $dom = NULL;
        $html = (string)$response->getSource();
        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace($re, "&amp;", $html);
        
        $dom = DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-2 div.card-header h4'));
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-2 div.card-body table.table tr')), 5);
        
    }

    function testActionDiscussionNew() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', ["action" => "discussion_new"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
    }
    
    function testActionDiscussionNewAdmin() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', ["action" => "discussion_new"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 3);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 4); //last item aint link
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3[data-binder-id] div.card-header h4'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3[data-binder-id] div.card-body table.table'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3[data-binder-id] div.card-body table.table'));
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3[data-binder-id] div.card-body table.table tr')), 8);
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3[data-binder-id] div.card-footer.text-right button.btn.btn-lg.binder-save-btn'));
    }

    function testActionEventNew() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', ["action" => "event_new"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
    }
    
    function testActionEventNewAdmin() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', ["action" => "event_new"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 3);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 4); //last item aint link
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-header h4'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive'));
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr th')), 10);
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr[data-binder-id] td')), 10);
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr[data-binder-id] td.btn-group button.btn.btn-sm.btn-outline-danger')); //delete new event row button
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body div.text-center button.btn.btn-sm.btn-outline-success i.fa.fa-plus')), 3); //three icons to duplicate event row
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-footer.text-right button.btn.btn-lg.binder-save-all-btn')); //save all button
    }

    function testActionPollNew() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', ["action" => "poll_new"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
    }
    
    function testActionPollNewAdmin() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', ["action" => "poll_new"]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string)$response->getSource();
        $dom = DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 3);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 4); //last item aint link
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-header h4'));
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive'));
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr th')), 15);
        Assert::equal(count($dom->find('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr[data-binder-id] td')), 15);
        
        Assert::true($dom->has('div.container.settings div.row div.col div.card.sh-box.my-3 div.card-body table.table.table-xs.table-hover.table-responsive tr[data-binder-id] td.btn-group button.btn.btn-sm.btn-primary.binder-save-btn'));
    }

}

$test = new SettingsPresenterTest($container);
$test->run();
