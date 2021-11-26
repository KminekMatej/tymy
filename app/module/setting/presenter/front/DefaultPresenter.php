<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tapi\UserResource;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;

class DefaultPresenter extends SettingBasePresenter
{

    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public OptionManager $optionManager;

    /** @inject */
    public PermissionManager $permissionManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;


    public function renderDefault()
    {
        $this->template->accessibleSettings = $this->getAccessibleSettings();
    }

}