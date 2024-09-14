<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;

class DefaultPresenter extends SettingBasePresenter
{
    #[\Nette\DI\Attributes\Inject]
    public EventManager $eventManager;

    #[\Nette\DI\Attributes\Inject]
    public PollManager $pollManager;

    #[\Nette\DI\Attributes\Inject]
    public OptionManager $optionManager;

    #[\Nette\DI\Attributes\Inject]
    public PermissionManager $permissionManager;

    #[\Nette\DI\Attributes\Inject]
    public EventTypeManager $eventTypeManager;

    #[\Nette\DI\Attributes\Inject]
    public StatusManager $statusManager;


    public function renderDefault(): void
    {
        $this->template->accessibleSettings = $this->getAccessibleSettings();
    }
}
