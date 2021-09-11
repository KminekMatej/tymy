<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Setting\Presenter\Front\SettingDefaultPresenter;

class MultiaccountPresenter extends SettingDefaultPresenter
{

    public function renderDefault()
    {
        $this->setLevelCaptions(["3" => ["caption" => $this->translator->translate("settings.multiaccount", 1), "link" => $this->link(":Setting:multiaccounts")]]);
        $this->template->multiaccounts = $this->multiAccountManager->getList();
    }

    public function handleMultiaccountRemove($team)
    {
        $this->multiAccountManager->delete($team);
        $this->flashMessage($this->translator->translate("common.alerts.multiaccountRemoved", NULL, ['team' => $team]), "success");
        $this->redirect("Settings:multiaccount");
    }

    public function createComponentAddMaForm()
    {
        $form = new Form();
        $form->addText("sysName", $this->translator->translate("team.team", 1));
        $form->addText("username", $this->translator->translate("sign.username"));
        $form->addPassword("password", $this->translator->translate("sign.password"));
        $form->addSubmit("save");
        $multiAccountManager = $this->multiAccountManager;
        $form->onSuccess[] = function (Form $form, stdClass $values) use ($multiAccountManager) {
            /* @var $multiAccountManager MultiaccountManager */
            $multiAccountManager->create([
                "login" => $values->username,
                "password" => $values->password,
                    ], $values->sysName);

            $this->flashMessage($this->translator->translate("common.alerts.multiaccountAdded", NULL, ["team" => $values->sysName]));
            $this->redirect("Settings:multiaccount");
        };
        return $form;
    }

}