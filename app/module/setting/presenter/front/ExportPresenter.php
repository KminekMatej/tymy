<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Settings\Manager\ICalManager;

class ExportPresenter extends SettingBasePresenter
{
    /** @inject */
    public ICalManager $iCalManager;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("settings.export"), $this->link(":Setting:Export:"));
    }

    public function renderDefault()
    {
        $myICal = $this->iCalManager->getByUserId($this->user->getId());

        $this->template->iCal = $myICal;
    }

    public function createComponentCalendarForm()
    {
        $iCal = $this->iCalManager->getByUserId($this->user->getId());

        $form = new Form();

        $form->addCheckbox("enabled", $this->translator->translate("settings.enableExport"));

        if ($iCal) {
            $form['enabled']->setValue($iCal->getEnabled());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) use ($iCal) {
            if ($iCal) {
                $this->iCalManager->update((array) $values, $this->user->getId(), $iCal->getId());
            } else {
                $this->iCalManager->create((array) $values);
            }

            $this->redirect('this');
        };

        return $form;
    }
}
