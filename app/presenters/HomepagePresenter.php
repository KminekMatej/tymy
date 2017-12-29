<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;
use App\Model; 
use Nette\Application\UI\Form;

class HomepagePresenter extends SecuredPresenter {

    public $navbar;
    
    /** @var \Tymy\Live @inject */
    public $live;
    
    public function beforeRender() {
        parent::beforeRender();
        $this->template->addFilter('lastLogin', function ($lastLogin) {
            $diff = date("U") - strtotime($lastLogin);
            if($diff == 1) return "před vteřinou";
            if($diff < 60) return "před $diff vteřinami";
            if($diff < 120) return "před minutou";
            $diffMinutes = round($diff / 60);
            if($diff < 1800) return "před $diffMinutes minutami";
            if($diff < 3600) return "před půl hodinou";
            if($diff < 7200) return "před hodinou";
            $diffHours = round($diff / 3600);
            if($diff < 86400) return "před $diffHours hodinami";
            $diffDays = round($diff / 86400);
            if($diff < 172800) return "před 1 dnem";
            return "před $diffDays dny";
        });
    }
    
    public function renderDefault() {
        try {
            $events = $this->eventList->setHalfYearFrom(NULL, NULL)->getData();
            $this->template->discussions = $this->discussionList->getData();
            $this->template->users = $this->sortUsersByLastLogin($this->users->getData());
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }

        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evMonths = $this->eventList->getAsMonthArray();
        $this->template->events = $this->eventList->getAsArray();
        $this->template->eventTypes = $this->eventTypeList;
        $this->template->liveUsers = $this->live->reset()->getData();
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
