<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Application\UI\Form;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Core\Factory\FormFactory;

class ExportPresenter extends EventBasePresenter
{
    /** @inject */
    public HistoryManager $historyManager;

    /** @inject */
    public FormFactory $formFactory;

    public function beforeRender()
    {
        parent::beforeRender();

        $this->addBreadcrumb($this->translator->translate("report.report", 1), $this->link(":Event:Export:"));
    }

    public function createComponentExportAttendanceForm()
    {
        return $this->formFactory->createExportAttendanceForm($this->link(":Api:Event:Export:attendance"));
    }
}
