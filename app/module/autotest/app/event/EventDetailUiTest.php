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
        $dom = parent::getDomForAction('default', ["resource" => $event["id"]]);
        
        
        
        //has navbar
        parent::assertDomHas($dom, 'div#snippet-navbar-nav');

        //has breadcrumbs
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Docházka", true);
        $this->assertBreadcrumb($dom, 2, $event["caption"], false);

        $eventDom = parent::assertDomHas($dom, 'div.container-fluid.event');
        $eventBoxDom = parent::assertDomHas($eventDom, 'div.card.sh-box', expectedCount: 2);
        $attendanceBoxDom = parent::assertDomHas($eventDom, 'div.card.sh-box#snippet--attendanceTabs');

        //eventBox: header
        $eventBoxHeader = parent::assertDomHas($eventBoxDom, 'div.card-header');
        parent::assertDomHas($eventBoxHeader, 'h4.card-title');
        parent::assertDomHas($eventBoxHeader, 'a.btn.btn-outline-dark.ajax#snippet--historyBtn');

        //eventBox: body
        $eventBoxBody = parent::assertDomHas($eventBoxDom, 'div.card-body');
        parent::assertDomHas($eventBoxBody, 'h6.card-subtitle.text-muted');
        parent::assertDomHas($eventBoxBody, 'div.row div.col-lg-4 table.table.mb-0 tr th', expectedCount: 3);
        parent::assertDomHas($eventBoxBody, 'div.row div.col-lg-4 table.table.mb-0 tr td', expectedCount: 3);
        parent::assertDomHas($eventBoxBody, 'div.row div.col-lg-8.d-flex.flex-column.align-items-center div#snippet--attendanceWarning');
        parent::assertDomHas($eventBoxBody, 'div.row div.col-lg-8.d-flex.flex-column.align-items-center input[name=preStatusDesc]');
        parent::assertDomHas($eventBoxBody, 'div.row div.col-lg-8.d-flex.flex-column.align-items-center div.custom-container.btn-group.input-group.pt-2'); //attendance button group
        parent::assertDomHas($eventBoxBody, 'div.row div.col-lg-8.d-flex.flex-column.align-items-center div.custom-container.btn-group.input-group.pt-2 button.custom-btn-sm', expectedCount: 3);

        //attendanceBox
        parent::assertDomHas($attendanceBoxDom, 'div.card-header ul.nav li.nav-item', expectedCount: 1);
        $attendancePane = parent::assertDomHas($attendanceBoxDom, 'div.card-body.tab-content div.tab-pane', expectedCount: 1);

        $this->authorizeAdmin();
        $this->recordManager->deleteEvent($event["id"]);
    }

    protected function getPresenter(): string
    {
        return "Detail";
    }

    public function getModule(): string
    {
        return "Event";
    }
}

$test = new EventDetailUiTest($container);
$test->run();
