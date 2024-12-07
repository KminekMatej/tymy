<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Team;

use Nette;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class TeamUiTest extends UITest
{
    public function testPlayers()
    {
        $this->authorizeUser();
        $dom = parent::getDomForAction('players');

//has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));

        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Hráči", false);

        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3);

        Assert::true($dom->has('div.container-fluid.users'));
        parent::assertDomHas($dom, 'div.container-fluid.users div.row#snippet--userList div.col-md-3.my-2 div.card.sh-box', expectedCount: 14);
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
