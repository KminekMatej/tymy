<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Event;

use Tester\Assert;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();
class EventDetailUiTest extends UITest
{
    public function testEvent()
    {
        $this->authorizeAdmin();
        $event = $this->recordManager->createEvent();

        $this->authorizeUser();
        $dom = parent::getDomForAction('default', ["event" => $event["id"]]);
//has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
//has breadcrumbs
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3);
//last item aint link
        //test body
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-header div.row div.col h4.card-title'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body h6.card-subtitle.mb-2.text-muted span a'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body p.card-text'));
        Assert::equal(count($dom->find('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-lg-4 table.table.mb-0 tr th')), 3);
        Assert::equal(count($dom->find('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-lg-4 table.table.mb-0 tr td')), 3);
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-lg-8.d-flex.flex-column-reverse.align-items-center input.form-control.form-control-sm.custom-btn-sm'));
        Assert::count(3, $dom->find('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-lg-8.d-flex.flex-column-reverse.align-items-center button.btn.custom-btn-sm'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3 div.card-body div.row div.col-lg-8.d-flex.flex-column-reverse.align-items-center div#snippet--attendanceWarning'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3#snippet--attendanceTabs div.card-header ul.nav.nav-tabs.flex-column.flex-sm-row.card-header-tabs li.nav-item'));
        Assert::true($dom->has('div.container.event div.row div.col div.card.sh-box.my-3#snippet--attendanceTabs div.card-body div.tab-content div.tab-pane.fade.player-list div.row.my-2'));
//admin sees also pencil button
        $this->authorizeAdmin();
        $dom = parent::getDomForAction('event', ["udalost" => $event["id"]]);
        $pencilBtn = 'div.container.event div.row div.col div.card.sh-box.my-3 div.card-header div.row div.col.col-md-auto a.btn.btn-sm.btn-light.btn-light-bordered i.fa.fa-edit';
        Assert::true($dom->has($pencilBtn));
        $this->recordManager->deleteEvent($event["id"]);
    }

    protected function getPresenter(): string
    {
        return "Event";
    }

    public function getModule(): string
    {
        return "Event";
    }
}

$test = new EventDetailUiTest($container);
$test->run();
