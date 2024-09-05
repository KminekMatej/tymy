<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use Tracy\Debugger;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Exception\TymyResponse;
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

    public function actionNewType()
    {
        try {
            $this->eventTypeManager->create(["code" => $this->randomCode()]);
            $this->flashMessage($this->translator->translate("common.alerts.created"), 'success');
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:default");
    }

    public function actionNewStatusSet()
    {
        try {
            $this->statusSetManager->create([
                "name" => ""
            ]);
            $this->flashMessage($this->translator->translate("common.alerts.created"), 'success');
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:default");
    }

    public function actionNewStatus(int $ssid)
    {
        try {
            $this->statusManager->create([
                "caption" => "",
                "statusSetId" => $ssid,
                "code" => $this->randomCode(),
            ]);
            $this->flashMessage($this->translator->translate("common.alerts.created"), 'success');
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:default");
    }

    public function actionDeleteType(int $id)
    {
        try {
            $this->eventTypeManager->delete($id);
            $this->flashMessage($this->translator->translate("common.alerts.deleted"), 'success');
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:default");
    }

    public function actionDeleteStatusSet(int $ssid)
    {
        try {
            $this->statusSetManager->delete($ssid);
            $this->flashMessage($this->translator->translate("common.alerts.deleted"), 'success');
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:default");
    }

    public function actionDeleteStatus(int $sid)
    {
        try {
            $this->statusManager->delete($sid);
            $this->flashMessage($this->translator->translate("common.alerts.deleted"), 'success');
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:default");
    }

    public function createComponentStatusSetForm(): Form
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
        $allStatusSets = $this->statusSetManager->getList();

        try {
            foreach ($allStatusSets as $statusSet) {
                assert($statusSet instanceof StatusSet);
                $id = $statusSet->getId();

                $updates = [];
                if ($values->{"statusSet_{$id}_name"} !== $statusSet->getName()) {
                    $updates["name"] = $values->{"statusSet_{$id}_name"};
                }
                if (intval($values->{"statusSet_{$id}_order"}) !== $statusSet->getOrder()) {
                    $updates["order"] = intval($values->{"statusSet_{$id}_order"});
                }
                if (!empty($updates)) {
                    $this->statusSetManager->updateByArray($id, $updates);
                }
            }

            //update statuses
            $allStatuses = $this->statusManager->getList();
            foreach ($allStatuses as $status) {
                /* @var $status Status */
                $id = $status->getId();
                $updates = [];
                foreach (["caption", "code", "color", "icon", "order"] as $field) {
                    $val = $values->{"status_{$id}_{$field}"};
                    if ($field == "color") {
                        $val = ltrim($val, " #");
                    }
                    $getter = "get" . ucfirst($field);
                    if ($val != $status->$getter()) {
                        $updates[$field] = $val;
                    }
                }
                if (!empty($updates)) {
                    $this->statusManager->updateByArray($id, $updates);
                }
            }

            $this->flashMessage($this->translator->translate("common.alerts.configSaved"));
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }

        $this->redirect(":Setting:Team:");
    }

    public function eventTypeFormSuccess(Form $form, $values): void
    {
        $allIds = array_keys($this->eventTypeManager->getIdList());

        foreach ($allIds as $typeId) {
            $this->eventTypeManager->updateByArray(
                $typeId,
                [
                "code" => $values->{$typeId . "_code"},
                "caption" => $values->{$typeId . "_caption"},
                "order" => $values->{$typeId . "_order"},
                "color" => ltrim($values->{$typeId . "_color"}, " #"),
                "preStatusSetId" => $values->{$typeId . "_preStatusSet"},
                "postStatusSetId" => $values->{$typeId . "_postStatusSet"},
                ]
            );
        }

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

    /**
     * Generate and return random 3-char code
     * @return string
     */
    private function randomCode(): string
    {
        return strtoupper(substr(md5(random_int(0, 1000)), 0, 3));
    }
}
