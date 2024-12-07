<?php

namespace Tymy\Module\Autotest;

use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Tester\DomQuery;
use Tymy\Module\Autotest\Entity\Assert;

abstract class UITest extends RequestCase
{
    protected IPresenter $presenter;
    protected string $presenterName;

    abstract protected function getPresenter(): string;

    protected function setUp()
    {
        $this->presenterName = "{$this->getModule()}:{$this->getPresenter()}";
        $this->presenter = $this->presenterFactory->createPresenter($this->presenterName);
        assert($this->presenter instanceof Presenter);
        $this->presenter->autoCanonicalize = false;

        parent::setUp();
    }

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

        $html = (string) $response->getSource();
        return DomQuery::fromHtml($html);
    }

    /**
     * Asserts that given selector exists in Dom and return $returnIndex's found instance as DomQuery object
     *
     * @param DomQuery $dom
     * @param string $selector
     * @param int|null $returnIndex Index which found element to return
     * @param int|null $expectedCount If set, asserts how many items are expected to return
     * @return DomQuery
     */
    protected function assertDomHas(DomQuery $dom, string $selector, ?int $returnIndex = 0, ?int $expectedCount = null): DomQuery
    {
        Assert::true($dom->has($selector), "Selector `$selector` not found in HTML output");
        $items = $dom->find($selector);

        if ($expectedCount) {
            Assert::count($expectedCount, $items, "Selector `$selector` count not as expected");
        }

        return $items[$returnIndex];
    }

    /**
     * Assert breadcrumb number, caption and hyperlink
     *
     * @param DomQuery $dom
     * @param int $index
     * @param string $caption
     * @param bool $isLink
     * @return void
     */
    protected function assertBreadcrumb(DomQuery $dom, int $index, string $caption, bool $isLink = false): void
    {
        Assert::true($dom->has('div.container div.row div.col ol.breadcrumb'));
        $breadcrumb = self::assertDomHas($dom, 'ol.breadcrumb li.breadcrumb-item', $index);

        if ($isLink) {
            Assert::equal($caption, $breadcrumb->a->__toString(), "Breadcrumb #$index caption is invalid");
            Assert::true(isset($breadcrumb->a), "Breadcrumb #$index:$caption is not hyperlink!");
        } else {
            Assert::equal($caption, $breadcrumb->__toString(), "Breadcrumb #$index caption is invalid");
            Assert::false(isset($breadcrumb->a), "Breadcrumb #$index:$caption shouldn't be hyperlink!");
        }
    }
}
