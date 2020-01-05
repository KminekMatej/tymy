<?php

namespace App\Presenters;

use Tapi\Exception\APIException;
use Tapi\MultiaccountTransferKeyResource;
use Tapi\NewsListResource;
use Tapi\UsersLiveResource;

class HomepagePresenter extends SecuredPresenter {

    public $navbar;
    
    /** @var UsersLiveResource @inject */
    public $live;
    
    /** @var MultiaccountTransferKeyResource @inject */
    public $tkResource;
    
    /** @var NewsListResource @inject */
    public $newsResource;
    
    public function beforeRender() {
        parent::beforeRender();
        $this->template->addFilter('lastLogin', function ($lastLogin) {
            $diff = date("U") - strtotime($lastLogin);
            if($diff == 1) return $this->translator->translate("common.lastLogin.secondAgo");
            if($diff < 60) return $this->translator->translate("common.lastLogin.secondsAgo", NULL, ['n' => $diff]);
            if($diff < 120) return $this->translator->translate("common.lastLogin.minuteAgo");
            $diffMinutes = round($diff / 60);
            if($diff < 1800) return $this->translator->translate("common.lastLogin.minutesAgo", NULL, ['n' => $diffMinutes]);
            if($diff < 3600) return $this->translator->translate("common.lastLogin.halfHourAgo");
            if($diff < 7200) return $this->translator->translate("common.lastLogin.hourAgo");
            $diffHours = round($diff / 3600);
            if($diff < 86400) return $this->translator->translate("common.lastLogin.hoursAgo", NULL, ['n' => $diffHours]);
            $diffDays = round($diff / 86400);
            if($diff < 172800) return $this->translator->translate("common.lastLogin.dayAgo");
            return $this->translator->translate("common.lastLogin.daysAgo", NULL, ['n' => $diffDays]);;
        });
        
        $this->template->addFilter('namedayToday', function ($name, $webname) {
            return $this->translator->translate("team.hasNamedayToday", NULL, ["name" => '<strong><a href='.$this->link("Team:player", $webname).'>'.$name.'</a></strong>']);
        });
        $this->template->addFilter('namedayTommorow', function ($name, $webname) {
            return $this->translator->translate("team.hasNamedayTommorow", NULL, ["name" => '<strong><a href='.$this->link("Team:player", $webname).'>'.$name.'</a></strong>']);
        });
        $this->template->addFilter('birthdayToday', function ($name, $webname, $year) {
            return $this->translator->translate("team.hasBirthdayToday", NULL, ["name" => '<strong><a href='.$this->link("Team:player", $webname).'>'.$name.'</a></strong>', "year" => '<strong>'.$year.'.</strong>']);
        });
        $this->template->addFilter('birthdayTommorow', function ($name, $webname, $year) {
            return $this->translator->translate("team.hasBirthdayTommorow", NULL, ["name" => '<strong><a href='.$this->link("Team:player", $webname).'>'.$name.'</a></strong>', "year" => '<strong>'.$year.'.</strong>']);
        });
        
    }
    
    public function renderDefault() {
        parent::showNotes();
        try {
            $this->eventList->init()->setHalfYearFrom(NULL, NULL)->getData();
            $this->template->liveUsers = $this->live->init()->getData();
            $this->template->discussions = $this->discussionList->init()->getData();
            $this->template->debts = $this->debtList->init()->getData();
            $this->template->users = $this->sortUsersByLastLogin($this->userList->init()->getData());
            $this->template->notices = $this->newsResource->init()->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->template->today = date('m-d');
        $this->template->tommorow = date('m-d', strtotime('+ 1 day'));
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evMonths = $this->eventList->getAsMonthArray();
        $this->template->events = $this->eventList->getAsArray();
        $this->template->eventTypes = $this->eventTypeList;
        
        $this->template->neverLogin = $this->translator->translate("common.never");
    }

    public function actionJump($teamSysName, $hasV2){
        $tk = $this->tkResource->init()->setTeam($teamSysName)->getData();
        if($hasV2){
            $url = "https://$teamSysName.tymy.cz/v2/sign/in?tk=" . $tk->transferKey;
        } else {
            $url = "http://$teamSysName.tymy.cz/?tk=".$tk->transferKey."&uid=".$tk->uid."&page=main";
        }
        $this->redirectUrl($url);
    }
    
    private function sortUsersByLastLogin($usersArray){
        $notSetValues = [];
        foreach ($usersArray as $key => $value) {
            if(!property_exists($value, "lastLogin")){
                $notSetValues[] = $value;
                unset($usersArray[$key]);
            }
        }
        usort($usersArray, array( $this, 'sortUsersComparer' ));
        return array_merge($usersArray, $notSetValues);
    }
    
    private static function sortUsersComparer($a, $b) {
        if (!property_exists($a, "lastLogin") || !property_exists($b, "lastLogin")) return 1;
        return strtotime($a->lastLogin) < strtotime($b->lastLogin) ? 1 : -1;
    }

}
