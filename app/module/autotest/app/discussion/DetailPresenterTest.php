<?php

namespace Tymy\Module\Autotest\Discussion;

use Nette;
use Nette\Utils\Strings;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class DiscussionPresenterTest extends UITest
{

    public function testActionDiscussionReadableWritable()
    {
        $this->authorizeAdmin();
        $discussion = $this->recordManager->createDiscussion();
        $discussionWebName = Strings::webalize($discussion["id"] . "-" . $discussion["caption"]);

        $this->authorizeUser(); //this user can read & write into th
        $request = new Nette\Application\Request($this->presenterName, 'GET', ['action' => 'default', 'discussion' => $discussionWebName]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $re = '/&(?!(?:apos|quot|[gl]t|amp);|#)/';

        $dom = NULL;
        $html = (string)$response->getSource();
        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace($re, "&amp;", $html);
        
        $dom = DomQuery::fromHtml($html);
        //has navbar
        parent::assertDomHas($dom,'div#snippet-navbar-nav');
        
        //has breadcrumbs
        
        parent::assertDomHas($dom,'div.container div.row div.col ol.breadcrumb');
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link

        $discussionDom = parent::assertDomHas($dom, 'div.container-fluid.my-2');
        Assert::equal(count($discussionDom->find('div.row.justify-content-md-center')), 2); //search container and wysiwyg container

        $wysiwygDom = parent::assertDomHas($discussionDom, 'div.row.justify-content-md-center', 0);
        $searchBarDom = parent::assertDomHas($discussionDom, 'div.row.justify-content-md-center', 1);

        //user can write, assert there is addPost textarea wysiwyg shown
        parent::assertDomHas($wysiwygDom, 'div.col-md-10 textarea#addPost');
        
        $searchFormDom = parent::assertDomHas($searchBarDom, 'div.col-md-10 div.addPost form.form-inline');
        parent::assertDomHas($searchFormDom, 'div.col-9 div.input-group input.form-control[name=search]');
        parent::assertDomHas($searchFormDom, 'div.col-9 div.input-group select.form-control.custom-select[name=suser]');
        parent::assertDomHas($searchFormDom, 'div.col-9 div.input-group button.form-control.btn.btn-outline-success.mr-sm-2.rounded-right');

        parent::assertDomHas($searchFormDom, 'div.col-3 button#editPost.btn.btn-warning');

        $discussionPostsDom = parent::assertDomHas($discussionDom, 'div.container-fluid.discussion#snippet--discussion');
        Assert::count(0, $discussionPostsDom->find('div.row'));
    }

    public function testActionDiscussionReadableOnly()
    {
        $this->authorizeAdmin();
        $discussion = $this->recordManager->createDiscussion(null, ["writeRightName" => "ADMINONLY"]);
        $discussionWebName = Strings::webalize($discussion["id"] . "-" . $discussion["caption"]);

        $this->authorizeUser(); //this user can read only in this discussion
        $request = new Nette\Application\Request($this->presenterName, 'GET', ['action' => 'default', 'discussion' => $discussionWebName]);
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        
        $re = '/&(?!(?:apos|quot|[gl]t|amp);|#)/';

        $dom = NULL;
        $html = (string)$response->getSource();
        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace($re, "&amp;", $html);
        
        $dom = DomQuery::fromHtml($html);
        //has navbar
        parent::assertDomHas($dom,'div#snippet-navbar-nav');
        
        //has breadcrumbs
        
        parent::assertDomHas($dom,'div.container div.row div.col ol.breadcrumb');
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item a[href]')), 2);
        Assert::equal(count($dom->find('ol.breadcrumb li.breadcrumb-item')), 3); //last item aint link

        $discussionDom = parent::assertDomHas($dom, 'div.container-fluid.my-2');
        Assert::falsey(count($dom->find('div.container.my-2 div.row.justify-content-md-center div.col-md-10 textarea#addPost'))); //user cannot write, assert there is not addPost textarea wysiwyg shown
        Assert::equal(count($discussionDom->find('div.row.justify-content-md-center')), 1); //only search container
        $searchBarDom = parent::assertDomHas($discussionDom, 'div.row.justify-content-md-center', 0);
        $searchFormDom = parent::assertDomHas($searchBarDom, 'div.col-md-10 div.addPost form.form-inline');
        parent::assertDomHas($searchFormDom, 'div.col-9 div.input-group input.form-control[name=search]');
        parent::assertDomHas($searchFormDom, 'div.col-9 div.input-group select.form-control.custom-select[name=suser]');
        parent::assertDomHas($searchFormDom, 'div.col-9 div.input-group button.form-control.btn.btn-outline-success.mr-sm-2.rounded-right');

        Assert::falsey(count($searchFormDom->find('div.col-3 button#editPost.btn.btn-warning'))); //no editPost button is shown in search bar

        $discussionPostsDom = parent::assertDomHas($discussionDom, 'div.container-fluid.discussion#snippet--discussion');
        Assert::count(0, $discussionPostsDom->find('div.row'));
    }

    protected function getPresenter(): string
    {
        return "Discussion";
    }

    public function getModule(): string
    {
        return "Discussion";
    }
}

(new DiscussionPresenterTest($container))->run();
