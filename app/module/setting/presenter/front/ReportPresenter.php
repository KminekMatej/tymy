<?php

namespace Tymy\Module\Setting\Presenter\Front;

class ReportPresenter extends SettingBasePresenter
{
    public function renderDefault()
    {
        //TODO
        $this->addBreadcrumb($this->translator->translate("report.report", 2), $this->link(":Setting:Report:"));
    }
}
