<?php

namespace Tymy\Module\Autotest\Discussion;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class DefaultPresenterTest extends UITest
{

    public function testActionDefault()
    {
        $this->authorizeUser();
        $request = new Request($this->presenterName, 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type(TextResponse::class, $response);
        
        $html = (string) $response->getSource();
        $dom = DomQuery::fromHtml($html);
        
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

(new DefaultPresenterTest($container))->run();
