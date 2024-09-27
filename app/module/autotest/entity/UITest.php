<?php

namespace Tymy\Module\Autotest;

use Tymy\Module\Core\Presenter\Front\BasePresenter;

abstract class UITest extends RequestCase
{
    protected BasePresenter $presenter;

    protected abstract function getPresenterName(): string;

    protected function setUp()
    {
        if ($this->getPresenterName() != "undefined") {
            $this->presenter = $this->presenterFactory->createPresenter($this->getPresenterName());
            $this->presenter->autoCanonicalize = false;
        }

        parent::setUp();
    }

}
