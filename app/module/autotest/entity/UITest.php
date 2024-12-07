<?php

namespace Tymy\Module\Autotest;

use Nette\Application\IPresenter;
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
     * Asserts that given selector exists in Dom and return $returnIndex's found instance as DomQuery object
     *
     * @param DomQuery $dom
     * @param string $selector
     * @return DomQuery
     */
    protected function assertDomHas(DomQuery $dom, string $selector, ?int $returnIndex = 0): DomQuery
    {
        Assert::true($dom->has($selector), "Selector `$selector` not found in HTML output");
        $items = $dom->find($selector);

        return $items[$returnIndex];
    }
}
