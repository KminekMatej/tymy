<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Factory\FormFactory;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Team\Manager\TeamManager;

class TeamPresenter extends SettingBasePresenter
{
    /** @inject */
    public StatusSetManager $statusSetManager;
    /** @inject */
    public FormFactory $formFactory;

    public function renderDefault()
    {
        $this->template->statusSets = $this->statusSetManager->getList();
    }

    public function createComponentStatusSetForm()
    {
        return $this->formFactory->createStatusSetForm([$this, 'statusFormSuccess']);
    }

    public function statusFormSuccess(Form $form, $values)
    {
        if (empty($values["id"])) {
            return;
        }

        //update status name 
        $this->statusSetManager->updateByArray(intval($values->id), ["name" => $values->name]);

        //update statuses
        /* @var $statusSet StatusSet */
        $statusSet = $this->statusSetManager->getById(intval($values->id));

        foreach ($statusSet->getStatuses() as $status) {
            $this->statusManager->updateByArray($status->getId(), [
                "caption" => $values->{"status_{$status->getId()}_caption"},
                "code" => $values->{"status_{$status->getId()}_code"},
                "color" => ltrim($values->{"status_{$status->getId()}_color"}, " #"),
            ]);
        }

        $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
        $this->redirect(":Setting:Team:");
    }

    public function createComponentTeamConfigForm()
    {
        $eventTypes = $this->eventTypeManager->getList();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), TeamManager::SKINS)->setValue($team->getSkin());
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), $this->userManager->getAllFields()["ALL"])->setValue($this->team->getRequiredFields());

        foreach ($eventTypes as $etype) {
            /* @var $etype EventType */
            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())
                ->setAttribute("type", "color")
                ->setAttribute("data-color", $etype->getColor())
                ->setValue('#' . $etype->getColor());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $teamData = $this->teamManager->getTeam();
            if ($teamData->getName() != $values->name ||
                $teamData->getSport() != $values->sport ||
                $teamData->getSkin() != $values->skin ||
                $teamData->getDefaultLanguageCode() != $values->defaultLanguage ||
                array_diff($values->requiredFields, $teamData->getRequiredFields()) || array_diff($teamData->getRequiredFields(), $values->requiredFields)
            ) {

                $this->teamManager->update([
                    "name" => $values->name,
                    "sport" => $values->sport,
                    "skin" => $values->skin,
                    "defaultLanguageCode" => $values->defaultLanguage,
                    "requiredFields" => join(",", $values->requiredFields),
                    ], $teamData->getId());
            }

            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect(":Setting:Team:");
        };
        return $form;
    }
}
