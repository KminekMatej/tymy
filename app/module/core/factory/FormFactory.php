<?php
namespace Tymy\Module\Core\Factory;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Model\Permission;

class FormFactory
{

    use Nette\SmartObject;

    private EventTypeManager $eventTypeManager;
    private EventManager $eventManager;
    private Translator $translator;

    public function __construct(EventTypeManager $eventTypeManager, EventManager $eventManager, Translator $translator)
    {
        $this->eventTypeManager = $eventTypeManager;
        $this->eventManager = $eventManager;
        $this->translator = $translator;
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

        $form = new Form;

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
}
