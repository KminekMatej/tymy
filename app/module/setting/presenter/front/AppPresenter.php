<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Core\Helper\CURLHelper;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;

class AppPresenter extends SettingBasePresenter
{
    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public UserManager $userManager;

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
        $this->addBreadcrumb($this->translator->translate("settings.application"), $this->link(":Setting:App:"));
    }

    public function renderDefault()
    {
        $currentVersion = $this->getCurrentVersion();
        $this->template->version = $currentVersion;
        $previousPatch = null;
        $firstMinor = null;
        foreach ($this->getVersions() as $version) {
            if (empty($previousPatch) && ($currentVersion->getMajor() !== $version->getMajor() || $currentVersion->getMinor() !== $version->getMinor() || $currentVersion->getPatch() !== $version->getPatch())) {
                $previousPatch = $version;
            }

            if (!isset($firstMinor) && $version->getMinor() !== $currentVersion->getMinor() && $version->getPatch() == 0) {
                $firstMinor = $version;
            }
        }
        if ($previousPatch === null && isset($version)) {
            $previousPatch = $version;  //latest version
        }
        $this->template->previousPatchVersion = $previousPatch;
        $this->template->firstMinorVersion = $firstMinor;

        $this->template->allSkins = $this->teamManager->allSkins;
        $this->getNextMilestone();
    }

    private function getNextMilestone()
    {
        $milestones = CURLHelper::get("https://api.github.com/repos/KminekMatej/tymy/milestones", true);
        $versions = ArrayHelper::pairs($milestones, "title", "html_url");

        uksort($versions, "version_compare");
        $nextVersion = array_key_first($versions);
        $this->template->nextMilestoneVersion = $nextVersion;
        $this->template->nextMilestoneUrl = $versions[$nextVersion];
    }

    public function createComponentUserConfigForm()
    {
        $form = new Form();
        $form->addSelect("skin", "Skin", $this->teamManager->allSkins)->setValue($this->skin);
        $form->addSubmit("save");

        $form->onSuccess[] = function (Form $form, stdClass $values) {
            $this->userManager->update(["skin" => $values->skin], $this->user->getId());
        };

        return $form;
    }
}
