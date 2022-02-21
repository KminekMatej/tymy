<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tracy\Debugger;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Factory\FormFactory;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Team\Manager\TeamManager;

class StatusPresenter extends SettingBasePresenter
{
    /** @inject */
    public StatusSetManager $statusSetManager;

    /** @inject */
    public StatusManager $statusManager;

    /** @inject */
    public FormFactory $formFactory;

    public function renderDefault()
    {
        $this->template->statusSets = $this->statusSetManager->getList();
    }

    public function renderDetail(string $statusSet)
    {
        $this->template->statusSets = $this->statusSetManager->getList();
    }

    public function createComponentStatusSetForm()
    {
        return $this->formFactory->createStatusSetForm([]);
    }

    public function createComponentStatusConfigForm()
    {
        $eventTypes = $this->eventTypeManager->getList();
        $statusList = $this->statusManager->getByStatusCode();
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

        foreach ($statusList as $code => $status) {
            /* @var $status Status */
            $form->addText("statusColor_$code", $status->getCode() . ": " . $status->getCaption())
                ->setAttribute("type", "color")
                ->setAttribute("data-color", $status->getColor())
                ->setValue('#' . $status->getColor());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) {
            Debugger::barDump($values);
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
            
            //update status colors
            foreach ($values as $key => $value) {
                Debugger::barDump($value, $key);
                if(strpos($key, "statusColor_") === 0){
                    $this->statusManager->updateByArray($id, $array);
                }
            }

            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
            $this->redirect(":Setting:Team:");
        };
        return $form;
    }
}
