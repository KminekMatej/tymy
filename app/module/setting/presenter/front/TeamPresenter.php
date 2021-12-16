<?php
namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tapi\UserResource;
use Tymy\Module\Event\Model\EventType;

class TeamPresenter extends SettingBasePresenter
{

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
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), $this->supplier->getAllSkins())->setValue($team->getSkin());
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), $this->userManager->getAllFields()["ALL"])->setValue($teamNeon->userRequiredFields);

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

            //check if there is some TAPI change to be commited
            $teamData = $this->teamManager->getTeam();
            if ($teamData->getName() != $values->name || $teamData->getSport() != $values->sport ||  $teamData->getSkin() != $values->skin || $teamData->getDefaultLanguageCode() != $values->defaultLanguage) {
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
