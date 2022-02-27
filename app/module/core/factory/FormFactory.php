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
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Model\Permission;

class FormFactory
{
    use Nette\SmartObject;

    private EventTypeManager $eventTypeManager;
    private StatusSetManager $statusSetManager;
    private EventManager $eventManager;
    private Translator $translator;

    public function __construct(EventTypeManager $eventTypeManager, EventManager $eventManager, Translator $translator, StatusSetManager $statusSetManager)
    {
        $this->eventTypeManager = $eventTypeManager;
        $this->eventManager = $eventManager;
        $this->translator = $translator;
        $this->statusSetManager = $statusSetManager;
    }

    /**
     * @return Form
     */
    public function createEventLineForm(array $eventTypesList, array $userPermissions, array $onSuccess): Form
    {
        $permissions = [];
        $eventTypes = [];

        foreach ($eventTypesList as $eventType) {
            /* @var $eventType EventType */
            $eventTypes[$eventType->getCode()] = $eventType->getCaption();
        }

        foreach ($userPermissions as $userPermission) {
            /* @var $userPermission Permission */
            $permissions[$userPermission->getName()] = $userPermission->getCaption();
        }

        $form = new Form();

        //     $id = $form->addHidden("id", $id);

        $type = $form->addSelect("type", null, $eventTypes)->setRequired();
        $caption = $form->addText("caption")->setRequired();
        $description = $form->addTextArea("description", null, null, 1);
        $start = $form->addText("startTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 24 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $end = $form->addText("endTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 25 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $close = $form->addText("closeTime")->setHtmlType("datetime-local")->setValue((new DateTime("+ 23 hours"))->format(BaseModel::DATETIME_ISO_NO_SECS_FORMAT))->setRequired();
        $place = $form->addText("place");
        $link = $form->addText("link");
        $canView = $form->addSelect("canView", null, $permissions)->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canPlan = $form->addSelect("canPlan", null, $permissions)->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");
        $canResult = $form->addSelect("canResult", null, $permissions)->setPrompt("-- " . $this->translator->translate("common.everyone") . " --");

        /* if (is_numeric($id)) {
          /* @var $event Event */
        /*    $event = $this->eventManager->getById($id);
          if ($event) {
          $type->setValue($event->getType());
          $caption->setValue($event->getCaption());
          $description->setValue($event->getDescription());
          $start->setValue($event->getStartTime());
          $end->setValue($event->getEndTime());
          $close->setValue($event->getCloseTime());
          $place->setValue($event->getPlace());
          $link->setValue($event->getLink());
          $canView->setValue($event->getCanView());
          $canPlan->setValue($event->getCanPlan());
          $canResult->setValue($event->getCanResult());
          }
          } */

        $form->addSubmit("save")->setHtmlAttribute("title", $this->translator->translate("common.saveAll"));
        $form->onSuccess[] = $onSuccess;

        return $form;

        /*
          return new Multiplier(function ($id) use ($eventTypes, $permissions, $onSuccess) {

          }); */
    }

    public function createStatusForm(array $statusSets, array $onSuccess)
    {
        $form = new Form();
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
                }
                $form->onSuccess[] = $onSuccess;
                return $form;
            });
    }
}
