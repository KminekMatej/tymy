<?php
namespace Tymy\Module\Core\Factory;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Utils\DateTime;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;

class FormFactory
{

    use Nette\SmartObject;

    private EventTypeManager $eventTypeManager;
    private EventManager $eventManager;

    public function __construct(EventTypeManager $eventTypeManager, EventManager $eventManager)
    {
        $this->eventTypeManager = $eventTypeManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @return Form
     */
    public function createEventLineForm(): Multiplier
    {
        $eventTypes = $this->eventTypeManager->getList();

        return new Multiplier(function ($id) use ($eventTypes) {
                $form = new Form;

                $id = $form->addHidden("id", $id);

                $type = $form->addSelect("type", null, $eventTypes);
                $caption = $form->addText("caption");
                $description = $form->addText("description");
                $start = $form->addText("start")->setHtmlType("datetime-local")->setValue(new DateTime("+ 24 hours"));
                $end = $form->addText("end")->setHtmlType("datetime-local")->setValue(new DateTime("+ 25 hours"));
                $close = $form->addText("close")->setHtmlType("datetime-local")->setValue(new DateTime("+ 23 hours"));
                $place = $form->addText("place");
                $link = $form->addText("link");
                $canView = $form->addSelect("canView");
                $canPlan = $form->addSelect("canPlan");
                $canResult = $form->addSelect("canResult");

                if (is_numeric($id)) {
                    /* @var $event Event */
                    $event = $this->eventManager->getById($id);
                    if ($event) {
                        $type->setValue($event->getType());
                        $caption->setValue($event->getCaption());
                        $description->setValue($event->getDescription());
                        $start->setDefaultValue($event->getStartTime());
                        $end->setDefaultValue($event->getEndTime());
                        $close->setDefaultValue($event->getCloseTime());
                        $place->setValue($event->getPlace());
                        $link->setValue($event->getLink());
                        $canView->setValue($event->getCanView());
                        $canPlan->setValue($event->getCanPlan());
                        $canResult->setValue($event->getCanResult());
                    }
                }

                return $form;
            });
    }
}
