<?php

namespace Tymy\Module\Core\Factory;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;

class FormFactory
{

    use Nette\SmartObject;
    private EventTypeManager $eventTypeManager;
    private StatusSetManager $statusSetManager;
    private EventManager $eventManager;
    private TeamManager $teamManager;
    private UserManager $userManager;
    private Translator $translator;

    public function __construct(EventTypeManager $eventTypeManager, EventManager $eventManager, Translator $translator, StatusSetManager $statusSetManager, TeamManager $teamManager, UserManager $userManager)
    {
        $this->eventTypeManager = $eventTypeManager;
        $this->eventManager = $eventManager;
        $this->teamManager = $teamManager;
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->statusSetManager = $statusSetManager;
    }

    /**
     * @return Form
     */
    public function createEventLineForm(array $eventTypesList, array $userPermissions, array $onSuccess, ?Event $event = null): Form
    {
        $permissions = [];
        $eventTypes = [];

        foreach ($eventTypesList as $eventType) {
            /* @var $eventType EventType */
            $eventTypes[$eventType->getId()] = $eventType->getCaption();
        }

        foreach ($userPermissions as $userPermission) {
            /* @var $userPermission Permission */
            $permissions[$userPermission->getName()] = $userPermission->getCaption();
        }

        $form = new Form();

        //     $id = $form->addHidden("id", $id);

        $type = $form->addSelect("eventTypeId", null, $eventTypes)->setHtmlAttribute("data-name", "eventTypeId")->setRequired();
        $caption = $form->addText("caption")->setHtmlAttribute("data-name", "caption")->setRequired();
        $description = $form->addTextArea("description", null, null, 1)->setHtmlAttribute("data-name", "description");
        $start = $form->addText("startTime")->setHtmlAttribute("data-name", "startTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 24 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $end = $form->addText("endTime")->setHtmlAttribute("data-name", "endTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 25 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $close = $form->addText("closeTime")->setHtmlAttribute("data-name", "closeTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 23 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $place = $form->addText("place")->setHtmlAttribute("data-name", "place");
        $link = $form->addText("link")->setHtmlAttribute("data-name", "link");
        $canView = $form->addSelect("canView", null, $permissions)->setHtmlAttribute("data-name", "canView")->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canPlan = $form->addSelect("canPlan", null, $permissions)->setHtmlAttribute("data-name", "canPlan")->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canResult = $form->addSelect("canResult", null, $permissions)->setHtmlAttribute("data-name", "canResult")->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");

        if ($event) {
            $form->addHidden("id", $event->getId());
            $type->setValue($event->getEventTypeId());
            $caption->setValue($event->getCaption());
            $description->setValue($event->getDescription());
            $start->setValue($event->getStartTime()->format(BaseModel::DATETIME_ISO_FORMAT));
            $end->setValue($event->getEndTime()->format(BaseModel::DATETIME_ISO_FORMAT));
            $close->setValue($event->getCloseTime()->format(BaseModel::DATETIME_ISO_FORMAT));
            $place->setValue($event->getPlace());
            $link->setValue($event->getLink());
            $canView->setValue($event->getViewRightName());
            $canPlan->setValue($event->getPlanRightName());
            $canResult->setValue($event->getResultRightName());
        }

        $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.saveAll"));
        $form->onSuccess[] = $onSuccess;

        return $form;
    }

    public function createStatusSetForm(array $onSuccess): Multiplier
    {
        return new Multiplier(function (string $statusSetId) use ($onSuccess) {
                /* @var $statusSet StatusSet */
                $statusSet = $this->statusSetManager->getById(intval($statusSetId));
                $form = new Form();
                $form->addHidden("id", $statusSetId);
                $form->addText("name", $this->translator->translate("settings.team"))->setValue($statusSet->getName())->setRequired();
                $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));

                foreach ($statusSet->getStatuses() as $status) {
                    /* @var $status Status */
                    $form->addText("status_{$status->getId()}_caption", $this->translator->translate("common.name"))
                        ->setValue($status->getCaption())
                        ->setHtmlAttribute("placeholder", $this->translator->translate("common.name"))
                        ->setRequired()
                        ->setMaxLength(50);
                    $form->addText("status_{$status->getId()}_code", $this->translator->translate("status.code"))
                        ->setValue($status->getCode())
                        ->setHtmlAttribute("placeholder", $this->translator->translate("status.code"))
                        ->setHtmlAttribute("size", "5")
                        ->setRequired()
                        ->setMaxLength(3);
                    $form->addText("status_{$status->getId()}_color", $this->translator->translate("status.color"))
                        ->setValue("#" . $status->getColor())
                        ->setHtmlAttribute("placeholder", $this->translator->translate("status.color"))
                        ->setRequired()
                        ->setMaxLength(6)
                        ->setHtmlAttribute("type", "color");
                    $form->addText("status_{$status->getId()}_icon", $this->translator->translate("status.icon"))
                        ->setValue($status->getIcon())
                        ->setHtmlAttribute("id", "iconpicker-{$status->getId()}")
                        ->setHtmlAttribute("data-toggle", "dropdown")
                        ->setHtmlAttribute("type", "hidden");
                }
                $form->onSuccess[] = $onSuccess;
                return $form;
            });
    }

    public function createEventTypeForm(array $onSuccess): Multiplier
    {
        $ssList = [];

        foreach ($this->statusSetManager->getIdList() as $statusSet) {
            /* @var $statusSet StatusSet */
            $ssList[$statusSet->getId()] = $statusSet->getName();
        }

        return new Multiplier(function (string $eventTypeId) use ($onSuccess, $ssList) {
                /* @var $eventType EventType */
                $eventType = $this->eventTypeManager->getById(intval($eventTypeId));
                $form = new Form();
                $form->addHidden("id", $eventTypeId);
                $form->addText("code", $this->translator->translate("status.code"))
                    ->setValue($eventType->getCode())
                    ->setHtmlAttribute("size", "5")
                    ->setRequired()
                    ->setMaxLength(3);
                $form->addText("caption", $this->translator->translate("common.name"))
                    ->setValue($eventType->getCaption())
                    ->setRequired();
                $form->addText("color", $this->translator->translate("status.color"))
                    ->setValue("#" . $eventType->getColor())
                    ->setMaxLength(6)
                    ->setHtmlAttribute("type", "color")
                    ->setRequired();

                $form->addSelect("preStatusSet", $this->translator->translate("status.preStatus"), $ssList)
                    ->setValue($eventType->getPreStatusSetId());
                $form->addSelect("postStatusSet", $this->translator->translate("status.postStatus"), $ssList)
                    ->setValue($eventType->getPostStatusSetId());

                $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.save"));
                $form->onSuccess[] = $onSuccess;
                return $form;
            });
    }

    public function createTeamConfigForm(array $onSuccess): Form
    {
        $eventTypes = $this->eventTypeManager->getList();
        $team = $this->teamManager->getTeam();

        $form = new Form();
        $form->addText("name", $this->translator->translate("team.name"))->setValue($team->getName());
        $form->addText("sport", $this->translator->translate("team.sport"))->setValue($team->getSport());
        $form->addSelect("defaultLanguage", $this->translator->translate("team.defaultLanguage"), ["CZ" => "Česky", "EN" => "English", "FR" => "Le français", "PL" => "Polski"])->setValue($team->getDefaultLanguageCode() ?: "CZ");
        $form->addSelect("skin", $this->translator->translate("team.defaultSkin"), TeamManager::SKINS)->setValue($team->getSkin());
        $form->addMultiSelect("requiredFields", $this->translator->translate("team.requiredFields"), $this->userManager->getAllFields()["ALL"])->setValue($team->getRequiredFields());

        foreach ($eventTypes as $etype) {
            /* @var $etype EventType */
            $form->addText("eventColor_" . $etype->getCode(), $etype->getCaption())
                ->setAttribute("type", "color")
                ->setAttribute("data-color", $etype->getColor())
                ->setValue('#' . $etype->getColor());
        }

        $form->addSubmit("save");

        $form->onSuccess[] = $onSuccess;

        return $form;
    }
}
