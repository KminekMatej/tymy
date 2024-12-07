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

class EventsUiTest extends UITest
{
    public function testActionDefault()
    {
        $this->authorizeAdmin();
        $eventId = $this->recordManager->createEvent()["id"];

        $dom = parent::getDomForAction();
//has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Docházka", false);

        Assert::true($dom->has('div.container-fluid.events'));
        Assert::true(count($dom->find('div.container-fluid.events div.row')) >= 1);
        Assert::true($dom->has('div.container-fluid.events div.row div.col-md-7.my-3 div.card.sh-box#calendar'));
        Assert::true($dom->has('div.container-fluid.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events-agenda'));
        Assert::equal(count($dom->find('div.container-fluid.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events-agenda div.card.sh-box.agenda[data-month]')), 13);

        $this->authorizeAdmin();
        $this->recordManager->deleteEvent($eventId);
    }

    protected function getPresenter(): string
    {
        return "Default";
    }

    public function getModule(): string
    {
        return "Event";
    }
}

$test = new EventsUiTest($container);
$test->run();
