<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Settings\Manager\ICalManager;

class ExportPresenter extends SettingBasePresenter
{
    /** @inject */
    public ICalManager $iCalManager;

    /** @inject */
    public StatusManager $statusManager;

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("settings.export"), $this->link(":Setting:Export:"));
    }

    public function renderDefault(): void
    {
        $this->template->iCal = $this->iCalManager->getByUserId($this->user->getId());
    }

    public function createComponentCalendarForm(): \Nette\Application\UI\Form
    {
        $iCal = $this->iCalManager->getByUserId($this->user->getId());

        $form = new Form();

        $enabled = $form->addCheckbox("enabled", $this->translator->translate("settings.enableExport"));
        $preStatuses = $this->statusManager->getAllPreStatuses();

        $statusArray = [];
        foreach ($preStatuses as $preStatus) {
            $statusArray[$preStatus->getId()] = $preStatus->getStatusSetName() . ": " . $preStatus->getCaption();
        }

        $items = $form->addMultiSelect("items", $this->translator->translate("settings.items"), $statusArray);

        if ($iCal !== null) {
            $enabled->setValue($iCal->getEnabled());
            $items->setValue($iCal->getStatusIds());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) use ($iCal): void {
            if ($iCal !== null) {
                $this->iCalManager->update((array) $values, $this->user->getId(), $iCal->getId());
            } else {
                $this->iCalManager->create((array) $values);
            }

            $this->redirect('this');
        };

        return $form;
    }
}
