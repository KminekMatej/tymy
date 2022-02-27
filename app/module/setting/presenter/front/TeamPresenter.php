<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
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

    public function createComponentStatusSetForm(): Form
    {
        return $this->formFactory->createStatusSetForm([$this, 'statusFormSuccess']);
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

    public function createComponentTeamConfigForm(): Form
    {
        return $this->formFactory->createStatusSetForm([$this, 'eventTypeFormSuccess']);
    }

    public function eventTypeFormSuccess(Form $form, $values): void
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
