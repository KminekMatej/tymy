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
        $dom = parent::getDomForAction();
//has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
//has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2);
//last item aint link

        Assert::true($dom->has('div.container.events'));
        Assert::true(count($dom->find('div.container.events div.row')) >= 1);
        Assert::true($dom->has('div.container.events div.row div.col-md-7.my-3 div.card.sh-box#calendar'));
        Assert::true($dom->has('div.container.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events-agenda'));
        Assert::equal(count($dom->find('div.container.events div.row div.col-md-5.my-3.agenda-wrapper#snippet--events-agenda div.card.sh-box.agenda[data-month]')), 13);
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
