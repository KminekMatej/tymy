<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Test\Event;

use Nette;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Test\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class PollsUiTest extends UITest
{
    public function testActionDefault()
    {
        $this->authorizeAdmin();
        $eventId = $this->recordManager->createPoll()["id"];

        $dom = parent::getDomForAction();
//has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Ankety", false);

        Assert::true($dom->has('div.container-fluid.polls'));
        Assert::true(count($dom->find('div.container-fluid.polls div.row')) >= 1);

        $this->authorizeAdmin();
        $this->recordManager->deletePoll($eventId);
    }

    protected function getPresenter(): string
    {
        return "Default";
    }

    public function getModule(): string
    {
        return "Poll";
    }
}

$test = new PollsUiTest($container);
$test->run();
