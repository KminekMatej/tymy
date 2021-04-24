<?php

namespace Tymy\App\Presenters;

use Nette\Application\UI\NavbarControl;
use Nette\Security\IUserStorage;
use Tracy\Debugger;
use Tymy\App\Model\SettingMenu;
use Tymy\Module\Debt\Manager\DebtManager;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of SecuredPresenter
 *
 * @author matej
 */
class SecuredPresenter extends BasePresenter {
    
    protected $levelCaptions;

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public DiscussionManager $discussionManager;

    /** @inject */
    public EventManager $eventManager;

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
    
    public $cacheStorage;
    
    public $accessibleSettings = [];
    
    public function getLevelCaptions(){
        return $this->levelCaptions;
    }
    
    public function setLevelCaptions($levelCaptions){
        if(!is_array($levelCaptions)) return false;
        foreach ($levelCaptions as $level => $caption) {
            $this->levelCaptions[$level] = $caption;
        }
        
        for ($index = max(array_keys($levelCaptions))+1; $index < count($this->levelCaptions); $index++) {
            unset($this->levelCaptions[$index]);
        }
    }
        
    protected function startup() {
        parent::startup();
        Debugger::$maxDepth = 7;
        if (!$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === IUserStorage::INACTIVITY) {
                $this->flashMessage($this->translator->translate("common.alerts.inactivityLogout"));
            }
            $this->redirect('Sign:in');
        }
        if(array_key_exists("language", $this->getUser()->getIdentity()->getData())){
            $this->translator->setLocale(self::LOCALES[$this->getUser()->getIdentity()->getData()["language"]]);
        }
        $this->supplier->loadUserNeon($this->getUser()->getId());
        
        $this->setAccessibleSettings();
        $this->setLevelCaptions(["0" => ["caption" => $this->translator->translate("common.mainPage"), "link" => $this->link("Homepage:")]]);
    }
    
    protected function createComponentNavbar() {
        $navbar = new NavbarControl($this, $this->pollManager, $this->discussionManager, $this->eventManager, $this->debtManager, $this->userManager, $this->multiaccountManager, $this->user, $this->teamManager);
        $navbar->redrawControl();
        return $navbar;
    }
    
    protected function parseIdFromWebname($webName){
        if(strpos($webName, "-")){
            return substr($webName,0,strpos($webName, "-"));
        }
        if(intval($webName))
            return intval($webName);
    }
    
    /**
     * Smart pagination script
     * @link https://stackoverflow.com/questions/163809/smart-pagination-algorithm
     * @param int $data Total count of items
     * @param int $limit Number of items per page
     * @param int $current Number of current page
     * @param int $adjacents Number of shown links
     * @return type
     */
    protected function pagination($data, $limit = null, $current = null, $adjacents = null) {
        $result = array();

        if (isset($data, $limit) === true) {
            $result = range(1, ceil($data / $limit));

            if (isset($current, $adjacents) === true) {
                if (($adjacents = floor($adjacents / 2) * 2 + 1) >= 1) {
                    $result = array_slice($result, max(0, min(count($result) - $adjacents, intval($current) - ceil($adjacents / 2))), $adjacents);
                }
            }
        }

        return $result;
    }
    
    public function getAccessibleSettings() {
        return $this->accessibleSettings;
    }

    private function setAccessibleSettings() {
        if($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("DSSETUP"))) $this->accessibleSettings[] = new SettingMenu("discussions", $this->translator->translate("discussion.discussion", 2), $this->link("Settings:discussions"), "fa-comments", TRUE);
        if($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('EVE_UPDATE'))) $this->accessibleSettings[] = new SettingMenu("events", $this->translator->translate("event.event", 2), $this->link("Settings:events"), "fa-calendar", TRUE);
        //TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("TEAM_UPDATE"))) $this->accessibleSettings[] = new SettingMenu("team", $this->translator->translate("team.team", 1), $this->link("Settings:team"), "fa-users", TRUE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('ASK.VOTE_UPDATE'))) $this->accessibleSettings[] = new SettingMenu("polls", $this->translator->translate("poll.poll", 2), $this->link("Settings:polls"), "fa-chart-pie", TRUE);
        $this->accessibleSettings[] = new SettingMenu("notes", $this->translator->translate("note.note", 2), $this->link("Settings:notes"), "fa-sticky-note", TRUE); //user can always manage at least his own notes
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS("REP_SETUP"))) $this->accessibleSettings[] = new SettingMenu("reports", $this->translator->translate("report.report", 2), $this->link("Settings:reports"), "fa-chart-area", FALSE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('IS_ADMIN'))) $this->accessibleSettings[] = new SettingMenu("permissions", $this->translator->translate("permission.permission", 2), $this->link("Settings:permissions"), "fa-gavel", TRUE);
        $this->accessibleSettings[] = new SettingMenu("multiaccounts", $this->translator->translate("settings.multiaccount", 1), $this->link("Settings:multiaccount"), "fa-sitemap", TRUE); //user can always look into multiaccount settings
        $this->accessibleSettings[] = new SettingMenu("app", $this->translator->translate("settings.application"), $this->link("Settings:app"), "fa-laptop", TRUE); //user can always look into app settings to setup his own properties
        return $this;
    }
    
    /*protected function showNotes($recordId = NULL) {
        $notesToShow = [];
        $presenterName = [
            'WELCOME' => "Homepage",
            'DISKUZE' => "Discussion",
            'UDALOST' => "Event",
            'ANKETA' => "Poll",
            'TYM' => "Team",
            'NASTAVENI' => "Settings",
            'POZNAMKY' => "Notes",
        ];
        if($this->template->noteList == null)
            return;
        foreach ($this->template->noteList as $note) {
            $display = explode(":", $note->specialPage);
            $displayPresenter = $display[0];
            $displayRule = NULL;
            $displayId = NULL;
            if(count($display) == 2){
                if(is_numeric($display[1])){
                    $displayId = $display[1];
                } else {
                    $displayRule = $display[1];
                }
            } else if (count($display) > 2){
                $displayId = $display[1];
                $displayRule = $display[2];
            }
            $displayPresenterPassed = array_key_exists($displayPresenter, $presenterName) && $presenterName[$displayPresenter] == $this->getRequest()->presenterName;
            switch ($displayRule) {
                case NULL:
                    $displayRulePassed = !array_key_exists('lastLogin', $this->getUser()->getIdentity()->getData()) && !$note->shown; //when rule is not filled, display only on first login
                    break;
                case "ALWAYS":
                    $displayRulePassed = TRUE;
                    break;
                case "NEW":
                    $displayRulePassed = $this->getUser()->getIdentity()->getData()["isNew"];
                    break;
                default:
                    $displayRulePassed = FALSE;
                    break;
            }
            $displayIdPassed = is_null($displayId) ? TRUE : ($this->getUser()->getId() == $displayId && $displayPresenter=="WELCOME") || $recordId == $displayId;
            if($displayPresenterPassed && $displayRulePassed && $displayIdPassed){
                $notesToShow[] = str_replace(":", "_", $note->specialPage);
                $note->shown = TRUE;
            }
        }
        $this->template->notesToShow = $notesToShow;
        $this->noteList->saveToCache();
    }*/
    
    protected function getAllRoles(){
        $allRoles = [];
        $allRoles[] = (object) ["code" => "SUPER", "caption" => $this->translator->translate("team.administrator"), "class" => $this->supplier->getRoleClass("SUPER")];
        $allRoles[] = (object) ["code" => "USR", "caption" => $this->translator->translate("team.userAdmin"), "class" => $this->supplier->getRoleClass("USR")];
        $allRoles[] = (object) ["code" => "ATT", "caption" => $this->translator->translate("team.attendanceAdmin"), "class" => $this->supplier->getRoleClass("ATT")];
        return $allRoles;
    }
    
    protected function redrawNavbar(){
        $this['navbar']->redrawControl("nav");
    }
}
