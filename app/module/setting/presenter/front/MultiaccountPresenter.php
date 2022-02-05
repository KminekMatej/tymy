<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class MultiaccountPresenter extends SettingBasePresenter
{
    public function renderDefault()
    {
        $this->setLevelCaptions(["3" => ["caption" => $this->translator->translate("settings.multiaccount", 1), "link" => $this->link(":Setting:Multiaccount:")]]);
        $this->template->multiaccounts = $this->multiaccountManager->getListUserAllowed();
    }

    public function handleMultiaccountRemove($team)
    {
        $this->multiaccountManager->delete($team);
        $this->flashMessage($this->translator->translate("common.alerts.multiaccountRemoved", null, ['team' => $team]), "success");
        $this->redirect(":Setting:Multiaccount:");
    }

    public function createComponentAddMaForm()
    {
        $form = new Form();
        $form->addText("sysName", $this->translator->translate("team.team", 1));
        $form->addText("username", $this->translator->translate("sign.username"));
        $form->addPassword("password", $this->translator->translate("sign.password"));
        $form->addSubmit("save");
        $multiaccountManager = $this->multiaccountManager;
        $form->onSuccess[] = function (Form $form, stdClass $values) use ($multiaccountManager) {
            /* @var $multiaccountManager MultiaccountManager */
            $multiaccountManager->create([
                "login" => $values->username,
                "password" => $values->password,
                    ], $values->sysName);

            $this->flashMessage($this->translator->translate("common.alerts.multiaccountAdded", null, ["team" => $values->sysName]));
            $this->redirect(":Setting:Multiaccount:");
        };
        return $form;
    }
}
