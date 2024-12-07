<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Test\Event;

use Tymy\Bootstrap;
use Tymy\Test\UITest;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class PollDetailUiTest extends UITest
{
    public function testEvent()
    {
        $this->authorizeAdmin();
        $poll = $this->recordManager->createPoll();

        $this->authorizeUser();
        $dom = parent::getDomForAction('default', ["resource" => $poll["id"]]);



        //has navbar
        parent::assertNavbar($dom);
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Ankety", true);
        $this->assertBreadcrumb($dom, 2, $poll["caption"], false);

        $objectDom = parent::assertDomHas($dom, 'div.container-fluid.poll');
        parent::assertDomHas($objectDom, 'div.card.sh-box', expectedCount: 2);

        $this->authorizeAdmin();
        $this->recordManager->deletePoll($poll["id"]);
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

$test = new PollDetailUiTest($container);
$test->run();
