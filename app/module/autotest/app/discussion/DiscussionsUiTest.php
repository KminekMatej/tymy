<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Discussion;

use Tester\Assert;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class DiscussionsUiTest extends UITest
{
    public function testActionDefault()
    {
        $this->authorizeAdmin();
        $this->recordManager->createDiscussion();

        $dom = parent::getDomForAction();

        //has navbar
        parent::assertDomHas($dom, 'div#snippet-navbar-nav');
        parent::assertDomHas($dom, 'div.container');
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Diskuze", false);

        $discussionsDom = parent::assertDomHas($dom, 'div.container-fluid.discussions');
        $discussionsListDom = parent::assertDomHas($discussionsDom, 'div.card.sh-box.discussion-box');
        Assert::true(count($discussionsListDom->find('div.card-body div.row')) >= 1);
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
