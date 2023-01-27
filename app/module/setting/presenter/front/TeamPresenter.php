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

    public function renderDefault(): void
    {
        $this->template->statusSets = $this->statusSetManager->getList();
    }

    public function createComponentStatusSetForm(): Multiplier
    {
        return $this->formFactory->createStatusSetForm(fn(Form $form, $values) => $this->statusFormSuccess($form, $values));
    }

    public function createComponentTeamConfigForm(): Form
    {
        return $this->formFactory->createTeamConfigForm(fn(Form $form, $values) => $this->teamConfigFormSuccess($form, $values));
    }

    public function createComponentEventTypeForm(): Form
    {
        return $this->formFactory->createEventTypeForm(fn(Form $form, $values) => $this->eventTypeFormSuccess($form, $values));
    }

    public function statusFormSuccess(Form $form, $values): void
    {
        if (empty($values["id"])) {
            return;
        }

        //update status name
        $this->statusSetManager->updateByArray((int) $values->id, ["name" => $values->name, "order" => $values->order]);

        //update statuses
        $statusSet = $this->statusSetManager->getById((int) $values->id);
        assert($statusSet instanceof StatusSet);

        foreach ($statusSet->getStatuses() as $status) {
            $this->statusManager->updateByArray($status->getId(), [
                "caption" => $values->{"status_{$status->getId()}_caption"},
                "code" => $values->{"status_{$status->getId()}_code"},
                "color" => ltrim($values->{"status_{$status->getId()}_color"}, " #"),
                "icon" => $values->{"status_{$status->getId()}_icon"},
                "order" => $values->{"status_{$status->getId()}_order"},
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
        $this->eventTypeManager->updateByArray((int) $values->id, [
            "code" => $values->code,
            "caption" => $values->caption,
            "order" => $values->order,
            "color" => ltrim($values->color, " #"),
            "preStatusSetId" => $values->preStatusSet,
            "postStatusSetId" => $values->postStatusSet,
        ]);

        $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
        $this->redirect(":Setting:Team:");
    }

    public function teamConfigFormSuccess(Form $form, $values): void
    {
        $teamData = $this->teamManager->getTeam();
        if (
            $teamData->getName() != $values->name ||
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
                "requiredFields" => implode(",", $values->requiredFields),
                ], $teamData->getId());
        }

        $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
        $this->redirect(":Setting:Team:");
    }
}
