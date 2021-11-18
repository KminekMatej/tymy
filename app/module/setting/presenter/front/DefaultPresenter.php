<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tapi\UserResource;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;

class DefaultPresenter extends SettingBasePresenter
{

    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public OptionManager $optionManager;

    /** @inject */
    public PermissionManager $permissionManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;


    public function renderDefault()
    {
        $this->template->accessibleSettings = $this->getAccessibleSettings();
    }

    public function createComponentUserConfigForm()
    {
        $form = new Form();
        $form->addSelect("skin", "Skin", $this->supplier->getAllSkins())->setValue($this->supplier->getSkin());
        $form->addSubmit("save");
        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $userNeon = $this->supplier->getUserNeon();
            $userNeon->skin = $values->skin;
            $this->supplier->saveUserNeon($this->getUser()->getId(), (array) $userNeon);
            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect("Settings:app");
        };
        return $form;
    }

}