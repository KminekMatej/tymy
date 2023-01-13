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

    public function renderDefault(): void
    {
    }
    
    public function createComponentExportAttendanceForm()
    {
        return $this->formFactory->createExportAttendanceForm($this->link(":Api:Event:Export:attendance"));
    }
}
