<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Team;

use Tester\Assert;
use Tester\Environment;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use const ROOT_DIR;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class TeamUiTest extends UITest
{
    public function getAllStatuses()
    {
        return [
            "players" => ["players", "Hráči", 2],
            "members" => ["members", "Členové", 1],
            "inits" => ["inits", "Registrovaní", 1],
            "sicks" => ["sicks", "Marodi", 1],
        ];
    }

    /**
     * @dataProvider getAllStatuses
     */
    public function testPlayers(string $action, string $caption, int $expectedCount)
    {
        Environment::lock('users', ROOT_DIR . "/temp");
        $this->authorizeUser();
        $dom = parent::getDomForAction($action);

        Assert::true($dom->has('div#snippet-navbar-nav'));
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, $caption, false);

        Assert::true($dom->has('div.container-fluid.users'));
        parent::assertDomHas($dom, 'div.container-fluid.users div.row#snippet--userList div.col-md-3.my-2 div.card.sh-box', expectedCount: $expectedCount);
    }

    public function testAll()
    {
        Environment::lock('users', ROOT_DIR . "/temp");
        $this->authorizeUser();
        $dom = parent::getDomForAction();

        Assert::true($dom->has('div#snippet-navbar-nav'));
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Všichni", false);

        Assert::true($dom->has('div.container-fluid.users'));
        parent::assertDomHas($dom, 'div.container-fluid.users div.row#snippet--userList div.col-md-3.my-2 div.card.sh-box', expectedCount: 5);
    }

    protected function getPresenter(): string
    {
        return "Default";
    }

    public function getModule(): string
    {
        return "Team";
    }
}

$test = new TeamUiTest($container);
$test->run();
