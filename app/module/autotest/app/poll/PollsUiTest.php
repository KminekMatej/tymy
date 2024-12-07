<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Event;

use Nette;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

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
//has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2);

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
