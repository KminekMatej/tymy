<?php

namespace Tymy\Module\Setting\Presenter\Front;

class ReportPresenter extends SettingBasePresenter
{
    public function renderDefault()
    {
        //TODO
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("report.report", 2), "link" => $this->link(":Setting:Report:")]]);
    }
}
