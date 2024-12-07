<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Test\Discussion;

use Tester\Assert;
use Tymy\Bootstrap;
use Tymy\Test\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class DiscussionsUiTest extends UITest
{
    public function testActionDefault()
    {
        $this->authorizeAdmin();
        $dId = $this->recordManager->createDiscussion()["id"];

        $dom = parent::getDomForAction();

        //has navbar
        parent::assertNavbar($dom);
        parent::assertDomHas($dom, 'div.container');
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Diskuze", false);

        $discussionsDom = parent::assertDomHas($dom, 'div.container-fluid.discussions');
        $discussionsListDom = parent::assertDomHas($discussionsDom, 'div.card.sh-box.discussion-box');
        Assert::true(count($discussionsListDom->find('div.card-body div.row')) >= 1);

        $this->authorizeAdmin();
        $this->recordManager->deleteDiscussion($dId);
    }

    protected function getPresenter(): string
    {
        return "Default";
    }

    public function getModule(): string
    {
        return "Discussion";
    }
}

(new DiscussionsUiTest($container))->run();
