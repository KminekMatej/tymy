<?php

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
        $dom = parent::getDomForAction($this->presenter);

        //has navbar
        parent::assertDomHas($dom, 'div#snippet-navbar-nav');

        //has breadcrumbs
        $containerDom = parent::assertDomHas($dom, 'div.container');
        parent::assertDomHas($containerDom, 'ol.breadcrumb');
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link

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
