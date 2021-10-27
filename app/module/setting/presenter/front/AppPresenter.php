<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;

class AppPresenter extends SettingDefaultPresenter
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


    public function actionApp()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("settings.application"), "link" => $this->link(":Setting:App:")]]);
        $currentVersion = $this->supplier->getVersion(0);
        $this->template->version = $currentVersion;
        $previousPatch = NULL;
        $firstMinor = NULL;
        foreach ($this->supplier->getVersions() as $version) {
            if (empty($previousPatch) && ($currentVersion->major != $version->major || $currentVersion->minor != $version->minor || $currentVersion->patch != $version->patch)) {
                $previousPatch = $version;
            }
            if ($currentVersion->major == $version->major && $currentVersion->minor == $version->minor && $version->patch == 0) {
                $firstMinor = $version;
            }
        }
        if ($previousPatch === NULL)
            $previousPatch = $this->supplier->getVersion(count($this->supplier->getVersions()));
        $this->template->previousPatchVersion = $previousPatch;
        $this->template->firstMinorVersion = $firstMinor;

        $this->template->allSkins = $this->supplier->getAllSkins();
    }

}