<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Module\Autotest\Discussion;

use Nette;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\Strings;
use Tester\Assert;
use Tester\DomQuery;
use Tymy\Bootstrap;
use Tymy\Module\Autotest\UITest;

use function count;

require getenv("ROOT_DIR") . '/app/Bootstrap.php';
$container = Bootstrap::boot();

class DiscussionDetailUiTest extends UITest
{
    /**
     * Load DOM response on presenter/action query
     *
     * @param string $action
     * @param array $params Additional request parameters
     * @return DomQuery
     */
    protected function getDomForAction(string $action = "default", array $params = [])
    {
        $this->authorizeUser();
        $request = new Request("{$this->getModule()}:{$this->getPresenter()}", 'GET', ['action' => $action] + $params);
        $response = $this->presenter->run($request);

        Assert::type(TextResponse::class, $response);
        assert($response instanceof TextResponse);

        //replace unescaped ampersands in html to prevent tests from failing
        $html = preg_replace('/&(?!(?:apos|quot|[gl]t|amp);|#)/', "&amp;", (string) $response->getSource());

        return DomQuery::fromHtml($html);
    }

    public function testActionDiscussionReadableWritable()
    {
        $this->authorizeAdmin();
        $discussion = $this->recordManager->createDiscussion();
        $discussionWebName = Strings::webalize($discussion["id"] . "-" . $discussion["caption"]);
        $dom = $this->getDomForAction('default', ['discussion' => $discussionWebName]);

        //has navbar
        parent::assertDomHas($dom, 'div#snippet-navbar-nav');

        //has breadcrumbs
        $this->assertBreadcrumb($dom, 0, "Hlavní stránka", true);
        $this->assertBreadcrumb($dom, 1, "Diskuze", true);
        $this->assertBreadcrumb($dom, 2, $discussion["caption"], false);

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

        $this->authorizeAdmin();
        $this->recordManager->deleteDiscussion($discussion["id"]);
    }

    public function testActionDiscussionReadableOnly()
    {
        $this->authorizeAdmin();
        $discussion = $this->recordManager->createDiscussion(null, ["writeRightName" => "ADMINONLY"]);
        $discussionWebName = Strings::webalize($discussion["id"] . "-" . $discussion["caption"]);

        $this->authorizeUser(); //this user can read only in this discussion
        $dom = $this->getDomForAction('default', ['discussion' => $discussionWebName]);

        //has navbar
        parent::assertDomHas($dom, 'div#snippet-navbar-nav');

        //has breadcrumbs

        parent::assertDomHas($dom, 'div.container div.row div.col ol.breadcrumb');
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

        $this->authorizeAdmin();
        $this->recordManager->deleteDiscussion($discussion["id"]);
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

(new DiscussionDetailUiTest($container))->run();
