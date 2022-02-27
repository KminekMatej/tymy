<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Factory\FormFactory;

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

    public function createComponentStatusSetForm(): Multiplier
    {
        return $this->formFactory->createStatusSetForm([$this, 'statusFormSuccess']);
    }
    
    public function createComponentTeamConfigForm(): Form
    {
        return $this->formFactory->createTeamConfigForm([$this, 'teamConfigFormSuccess']);
    }
    
    public function createComponentEventTypeForm(): Multiplier
    {
        return $this->formFactory->createEventTypeForm([$this, 'eventTypeFormSuccess']);
    }

    public function statusFormSuccess(Form $form, $values): void
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

    public function eventTypeFormSuccess(Form $form, $values): void
    {
        if (empty($values["id"])) {
            return;
        }

        //update eventTypes
        $this->eventTypeManager->updateByArray(intval($values->id), [
            "code" => $values->code,
            "caption" => $values->caption,
            "color" => $values->color,
            "pre_status_set" => $values->preStatusSet,
            "post_status_set" => $values->postStatusSet,
        ]);

        $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
        $this->redirect(":Setting:Team:");
    }

    public function teamConfigFormSuccess(Form $form, $values): void
    {
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
    }
}
