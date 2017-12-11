<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt'), $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class TeamPresenterTest extends IPresenterTest {

    const PRESENTERNAME = "Team";

    /** @var \Tymy\Users */
    private $users;
    private $counts;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
        $this->users = $this->container->getByType("\Tymy\Users");
    }

    protected function setUp() {
        $parentResult = parent::setUp();
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->counts = $this->users->reset()->getResult()->counts;
        return $parentResult;
    }

    function testActionDefault() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $html = (string) $response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);

        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
    }

    function getActions(){
        return [
            ["players", $this->counts["PLAYER"]],
            ["members", $this->counts["MEMBER"]],
            ["sicks", $this->counts["SICK"]]
        ];
    }
    
    /** @dataProvider getActions */
    function testAction($actionName, $itemsCount) {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => $actionName));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $html = (string) $response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);

        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link

        //has settings bar
        Assert::true($dom->has('div.container.users div.row.mt-2 div.col.text-right div.settings-bar a.btn.btn-outline-light i.fa.fa-envelope'), "Has email all button"); 
        
        //count displayed cards
        Assert::equal(count($dom->find('div.container.users div.row div.col-md-3.my-2 div.card.sh-box.set-box')), $itemsCount);
    }

    function allWebNames() {
        $users = $this->users->reset()
                ->getData();
        $inputArray = [];
        foreach ($users as $u) {
            $inputArray[] = [$u];
        }
        return $inputArray;
    }

    /**
     * @dataProvider allWebNames
     */
    function testPlayer($player) {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'player', "player" => $player->webName));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $html = (string) $response->getSource();
        $dom = Tester\DomQuery::fromHtml($html);

        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link

        Assert::equal(count($dom->find('div.container.user div.row div.col.my-3 div.card.sh-box div.card-header ul.nav.nav-tabs.card-header-tabs li.nav-item')), 5);
        Assert::equal(count($dom->find('div.container.user div.row div.col.my-3 div.card.sh-box div.card-body div.tab-content div.tab-pane.fade')), 5);
        Assert::equal(count($dom->find('div.container.user div.row div.col.my-3 div.card.sh-box div.card-body div.tab-content div.tab-pane.fade.active.show')), 1);
        
        //TODO : check all the elements on player page, but when the page is finished
        
        if($this->user->isAllowed('users','canDelete')){
            Assert::true($dom->has('div.container.user div.row div.col.my-3 div.card.sh-box div.card-footer.text-right button.btn.btn-lg.btn-danger.mx-2 i.fa.fa-times'));
        }
        Assert::true($dom->has('div.container.user div.row div.col.my-3 div.card.sh-box div.card-footer.text-right button.btn.btn-lg.btn-primary i.fa.fa-floppy-o'));
    }

}

$test = new TeamPresenterTest($container);
$test->run();
