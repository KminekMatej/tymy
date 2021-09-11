<?php

namespace Tymy\Module\Core\Presenter\Front;

use Nette\Application\UI\Form;
use Nette\Utils\Strings;
use stdClass;
use Tapi\UserResource;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Model\Option;
use Tymy\Module\Poll\Model\Poll;

class SettingPresenter extends SecuredPresenter
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


    public function actionApp()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("settings.application"), "link" => $this->link(":Setting:app")]]);
        $currentVersion = $this->supplier->getVersion(0);
        $this->template->version = $currentVersion;
        $previousPatch = NULL;
        $firstMinor = NULL;
        foreach ($this->supplier->getVersions() as $version) {
            if (empty($previousPatch) && ($currentVersion->major != $version->major || $currentVersion->minor != $version->minor || $currentVersion->patch != $version->patch)) {
                $previousPatch = $version;
            }
            if ($currentVersion->major == $version->major && $currentVersion->minor == $version->minor && $version->patch == 0) {
                $firstMinor = $version;
            }
        }
        if ($previousPatch === NULL)
            $previousPatch = $this->supplier->getVersion(count($this->supplier->getVersions()));
        $this->template->previousPatchVersion = $previousPatch;
        $this->template->firstMinorVersion = $firstMinor;

        $this->template->allSkins = $this->supplier->getAllSkins();
    }

    public function renderMultiaccount()
    {
        $this->setLevelCaptions(["3" => ["caption" => $this->translator->translate("settings.multiaccount", 1), "link" => $this->link(":Setting:multiaccounts")]]);
        $this->template->multiaccounts = $this->multiAccountManager->getList();
    }

    public function renderDefault()
    {
        $this->template->accessibleSettings = $this->getAccessibleSettings();
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

    public function createComponentTeamConfigForm()
    {
        $teamNeon = $this->supplier->getTeamNeon();
        $eventTypes = $this->eventTypeManager->getList();
        $statusList = $this->statusManager->getByStatusCode();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), $this->supplier->getAllSkins())->setValue($teamNeon->skin);
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), UserResource::getAllFields($this->translator)["ALL"])->setValue($teamNeon->userRequiredFields);

        foreach ($eventTypes as $etype) {
            /* @var $etype EventType */
            $color = isset($teamNeon->event_colors[$etype->getCode()]) ? $teamNeon->event_colors[$etype->getCode()] : "#bababa";

            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())->setAttribute("data-toggle", "colorpicker")->setAttribute("data-color", $color)->setValue($color);
        }

        foreach ($statusList as $status) {
            $color = $this->supplier->getStatusColor($status["code"]);
            $form->addText("statusColor_" . $status["code"], $status["caption"])->setAttribute("data-toggle", "colorpicker")->setAttribute("data-color", $color)->setValue($color);
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $teamNeon = $this->supplier->getTeamNeon();
            $teamNeon->skin = $values->skin;
            $teamNeon->userRequiredFields = $values->requiredFields;
            $eventColors = [];
            $statusColors = [];
            foreach ((array) $values as $name => $value) {
                $valData = explode("_", $name);
                if ($valData[0] == "eventColor") {
                    $eventColors[$valData[1]] = $value;
                }
                if ($valData[0] == "statusColor") {
                    $statusColors[$valData[1]] = $value;
                }
            }
            $teamNeon->event_colors = $eventColors;
            $teamNeon->status_colors = $statusColors;

            $this->supplier->saveTeamNeon((array) $teamNeon);
            $this->statusList->cleanCache();

            //check if there is some TAPI change to be commited
            $teamData = $this->teamManager->getTeam();
            if ($teamData->getName() != $values->name || $teamData->getSport() != $values->sport || $teamData->getDefaultLanguageCode() != $values->defaultLanguage) {
                $this->teamManager->update([
                    "name" => $values->name,
                    "sport" => $values->sport,
                    "defaultLanguageCode" => $values->defaultLanguage,
                        ], $teamData->getId());
            }

            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect("Settings:team");
        };
        return $form;
    }

}