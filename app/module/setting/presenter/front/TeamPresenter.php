<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Event\Model\EventType;

class TeamPresenter extends SettingBasePresenter
{
    public function createComponentTeamConfigForm()
    {
        $eventTypes = $this->eventTypeManager->getList();
        $statusList = $this->statusManager->getByStatusCode();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), $this->supplier->getAllSkins())->setValue($team->getSkin());
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), $this->userManager->getAllFields()["ALL"])->setValue($this->team->getRequiredFields());

        foreach ($eventTypes as $etype) {
            /* @var $etype EventType */
            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())->setAttribute("data-toggle", "colorpicker")->setAttribute("data-color", $etype->getColor())->setValue($etype->getColor());
        }

        foreach ($statusList as $status) {
            /* @var $status Status */
            $form->addText("statusColor_" . $status["code"], $status["caption"])->setAttribute("data-toggle", "colorpicker")->setAttribute("data-color", $status->getColor())->setValue($status->getColor());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $teamData = $this->teamManager->getTeam();
            if ($teamData->getName() != $values->name || $teamData->getSport() != $values->sport || $teamData->getSkin() != $values->skin || $teamData->getDefaultLanguageCode() != $values->defaultLanguage) {
                $this->teamManager->update([
                    "name" => $values->name,
                    "sport" => $values->sport,
                    "skin" => $values->skin,
                    "defaultLanguageCode" => $values->defaultLanguage,
                    ], $teamData->getId());
            }

            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect(":Setting:Team:");
        };
        return $form;
    }
}
