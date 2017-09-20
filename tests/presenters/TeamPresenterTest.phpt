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

    function testActionPlayers() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'players'));
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

        //count displayed cards
        Assert::equal(count($dom->find('div.container.users div.col-sm-3')), $this->counts["PLAYER"]);
    }

    function testActionMembers() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'members'));
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

        //count displayed cards
        Assert::equal(count($dom->find('div.container.users div.col-sm-3')), $this->counts["MEMBER"]);
    }

    function testActionSicks() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request(self::PRESENTERNAME, 'GET', array('action' => 'sicks'));
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

        //count displayed cards
        Assert::equal(count($dom->find('div.container.users div.col-sm-3')), $this->counts["SICK"]);
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
        var_dump($player->webName);
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
        Assert::equal(count($dom->find('div.container.user div.row div.col.my-3 div.card.sh-box div.card-block div.tab-content div.tab-pane.fade')), 5);
        Assert::equal(count($dom->find('div.container.user div.row div.col.my-3 div.card.sh-box div.card-block div.tab-content div.tab-pane.fade.active.show')), 1);
        
        Assert::true($dom->has('div.container.user div.row div.col.my-3 div.card.sh-box div.card-footer.text-right button.btn.btn-primary'));
    }

}

$test = new TeamPresenterTest($container);
$test->run();
