<?php

namespace Tymy\Module\Core\Presenter\Front;

use Nette\Security\IUserStorage;
use Tracy\Debugger;
use Tymy\Module\Core\Component\NavbarControl;
use Tymy\Module\Core\Model\SettingMenu;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

/**
 * Description of SecuredPresenter
 *
 * @author matej
 */
class SecuredPresenter extends BasePresenter
{
    protected $levelCaptions;

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public DiscussionManager $discussionManager;

    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public DebtManager $debtManager;

    /** @inject */
    public UserManager $userManager;

    /** @inject */
    public TeamManager $teamManager;

    /** @inject */
    public MultiaccountManager $multiaccountManager;
    public $discussionNews;
    public $apiRights;
    public $userRightsList;
    public $eventTypeList;
    public $noteList;
    public $statusList;
    public $accessibleSettings = [];

    public function getLevelCaptions()
    {
        return $this->levelCaptions;
    }

    public function addBreadcrumb(string $caption, ?string $link = null): void
    {
        $this->levelCaptions[] = [
            "caption" => $caption,
            "link" => $link,
        ];
    }

    public function beforeRender()
    {
        parent::beforeRender();
        
        if($this->tymyUser){
            
        }

        if ($this->tymyUser->getSkin()) {//set user defined skin instead of team one after login
            $this->template->skin = $this->skin = $this->tymyUser->getSkin();
        }
        $this->template->tymyUser = $this->tymyUser;

        $this->setAccessibleSettings();
        $this->addBreadcrumb($this->translator->translate("common.mainPage"), $this->link(":Core:Default:"));
    }

    protected function startup()
    {
        parent::startup();
        Debugger::$maxDepth = 7;
        if (!$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === IUserStorage::INACTIVITY) {
                $this->flashMessage($this->translator->translate("common.alerts.inactivityLogout"));
            }
            $this->redirect(':Sign:In:');
        }
    }

    protected function createComponentNavbar()
    {
        $navbar = new NavbarControl($this, $this->pollManager, $this->discussionManager, $this->eventManager, $this->debtManager, $this->userManager, $this->multiaccountManager, $this->user, $this->teamManager, $this->eventTypeManager);
        $navbar->redrawControl();
        return $navbar;
    }

    protected function parseIdFromWebname($webName)
    {
        if (strpos($webName, "-")) {
            return substr($webName, 0, strpos($webName, "-"));
        }
        if (intval($webName)) {
            return intval($webName);
        }
    }

    /**
     * Smart pagination script
     * @link https://stackoverflow.com/questions/163809/smart-pagination-algorithm
     * @param int $totalCount Total count of items
     * @param int $perPage Number of items per page
     * @param int $currentPage Number of current page
     * @param int $shownCount Number of shown links
     * @return array
     */
    protected function pagination(int $totalCount, int $perPage, int $currentPage, int $shownCount): array
    {
        if ($totalCount == 0) {
            return [];
        }

        $result = range(1, ceil($totalCount / $perPage));

        if (($shownCount = floor($shownCount / 2) * 2 + 1) >= 1) {
            $result = array_slice($result, max(0, min(count($result) - $shownCount, intval($currentPage) - ceil($shownCount / 2))), $shownCount);
        }

        return $result;
    }

    public function getAccessibleSettings()
    {
        return $this->accessibleSettings;
    }

    private function setAccessibleSettings()
    {
        $separate = false;
        if ($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("DSSETUP"))) {
            $this->accessibleSettings[] = new SettingMenu("discussions", $this->translator->translate("discussion.discussion", 2), $this->link(":Setting:Discussion:"), "fa-comments", true);
            $separate = true;
        }
        if (
            $this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('EVE_UPDATE')) ||
                $this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('EVE_CREATE')) ||
                $this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('EVE_DELETE'))
        ) {
            $this->accessibleSettings[] = new SettingMenu("events", $this->translator->translate("event.event", 2), $this->link(":Setting:Event:"), "fa-calendar", true);
            $separate = true;
        }

        if ($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("TEAM_UPDATE"))) {
            $this->accessibleSettings[] = new SettingMenu("team", $this->translator->translate("team.team", 1), $this->link(":Setting:Team:"), "fa-users", true);
            $separate = true;
        }

        if ($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('ASK.VOTE_UPDATE'))) {
            $this->accessibleSettings[] = new SettingMenu("polls", $this->translator->translate("poll.poll", 2), $this->link(":Setting:Poll:"), "fa-chart-pie", true);
            $separate = true;
        }

        /*if ($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("REP_SETUP"))) {
            $this->accessibleSettings[] = new SettingMenu("reports", $this->translator->translate("report.report", 2), $this->link(":Setting:Report:"), "fa-chart-area", false);
            $separate = true;
        }*/

        if ($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('IS_ADMIN'))) {
            $this->accessibleSettings[] = new SettingMenu("permissions", $this->translator->translate("permission.permission", 2), $this->link(":Setting:Permission:"), "fa-gavel", true);
            $separate = true;
        }

        if ($separate) {
            $this->accessibleSettings[] = new SettingMenu("separator"); //to separate user settings from admin settings
        }

        //user always accessible settings
        $this->accessibleSettings[] = new SettingMenu("multiaccounts", $this->translator->translate("settings.multiaccount", 1), $this->link(":Setting:Multiaccount:"), "fa-sitemap", true);
        $this->accessibleSettings[] = new SettingMenu("export", $this->translator->translate("settings.export", 1), $this->link(":Setting:Export:"), "far fa-calendar", true);
        $this->accessibleSettings[] = new SettingMenu("app", $this->translator->translate("settings.application"), $this->link(":Setting:App:"), "fa-laptop", true);

        return $this;
    }

    protected function getAllRoles(): array
    {
        return [
            (object) ["code" => "SUPER", "caption" => $this->translator->translate("team.administrator"), "class" => User::ROLE_SUPER_CLASS],
            (object) ["code" => "USR", "caption" => $this->translator->translate("team.userAdmin"), "class" => User::ROLE_USER_CLASS],
            (object) ["code" => "ATT", "caption" => $this->translator->translate("team.attendanceAdmin"), "class" => User::ROLE_ATTENDANCE_CLASS],
        ];
    }

    protected function redrawNavbar()
    {
        $this['navbar']->redrawControl("nav");
    }
}
