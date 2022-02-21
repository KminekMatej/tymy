<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;

class AppPresenter extends SettingBasePresenter
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

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("settings.application"), "link" => $this->link(":Setting:App:")]]);
    }

    public function renderDefault()
    {
        $currentVersion = $this->getCurrentVersion();
        $this->template->version = $currentVersion;
        $previousPatch = null;
        $firstMinor = null;
        foreach ($this->getVersions() as $version) {
            if (empty($previousPatch) && ($currentVersion->getMajor() != $version->getMajor() || $currentVersion->getMinor() != $version->getMinor() || $currentVersion->getPatch() != $version->getPatch())) {
                $previousPatch = $version;
            }

            if (!isset($firstMinor) && $version->getPatch() == 0) {
                $firstMinor = $version;
            }
        }
        if ($previousPatch === null) {
            $previousPatch = array_pop($this->getVersions());
        }
        $this->template->previousPatchVersion = $previousPatch;
        $this->template->firstMinorVersion = $firstMinor;

        $this->template->allSkins = $this->supplier->getAllSkins();
    }

    public function createComponentUserConfigForm()
    {
        $form = new Form();
        $form->addSelect("skin", "Skin", $this->supplier->getAllSkins())->setValue($this->skin);
        $form->addSubmit("save");
        return $form;
    }
}
