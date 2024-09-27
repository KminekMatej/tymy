<?php

namespace Tymy\Module\Autotest\Discussion;

use Nette;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class DiscussionPresenterTest extends UITest
{

    function testActionDefault()
    {
        $this->authorizeUser();
        $request = new Nette\Application\Request($this->getPresenterName(), 'GET', array('action' => 'default'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $html = (string) $response->getSource();
        $dom = DomQuery::fromHtml($html);
        
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        //has breadcrumbs
        Assert::true($dom->has('div.container'));
        Assert::true($dom->has('ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 1);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 2); //last item aint link
        
        Assert::true($dom->has('div.container.discussions'));
        Assert::true(count($dom->find('div.container.discussions div.row')) >= 1);
    }

    function testActionDiscussion($discussionName, $canWrite)
    {
        $this->authorizeAdmin();
        $discussion = $this->recordManager->createDiscussion();
        $discussionName = $discussion["name"];

        $this->authorizeUser();
        $request = new Nette\Application\Request($this->getPresenterName(), 'GET', ['action' => 'discussion', 'discussion' => $discussionName]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $re = '/&(?!(?:apos|quot|[gl]t|amp);|#)/';

        $dom = NULL;
        $html = (string)$response->getSource();
        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace($re, "&amp;", $html);
        
        $dom = DomQuery::fromHtml($html);
        //has navbar
        Assert::true($dom->has('div#snippet-navbar-nav'));
        
        //has breadcrumbs
        
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link
        
        Assert::equal(count($dom->find('div.container.my-2 div.row.justify-content-md-center')), $canWrite ? 2 : 1);
        if($canWrite){
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 textarea#addPost'));
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline input.form-control.mr-sm-2'));
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline span.mr-auto input.form-control.btn.btn-outline-success.mr-sm-2'));
            Assert::true($dom->has('div.container.my-2 div.row.justify-content-md-center div.col-md-10 div.addPost form.form-inline button.btn.btn-primary'));
        }
        
        Assert::true($dom->has('div.container.discussion#snippet--discussion'));
        Assert::true(count($dom->find('div.container.discussion#snippet--discussion div.row'))<=20);
    }

    protected function getPresenterName(): string
    {
        return "Discussion:Default";
    }

    public function getModule(): string
    {
        return "Discussion";
    }
}

(new DiscussionPresenterTest($container))->run();
