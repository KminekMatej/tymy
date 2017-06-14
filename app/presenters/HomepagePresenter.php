<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;
use App\Model; 
use Nette\Application\UI\Form;

class HomepagePresenter extends SecuredPresenter {

    public $navbar;
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["0" => ["caption" => "Přehled", "link" => $this->link("Homepage:")]]);
    }
    
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
        $eventsObj = new \Tymy\Events($this->tapiAuthenticator, $this);
        $events = $eventsObj->loadYearEvents(NULL, NULL);
        date("n", strtotime("2017-13-5"));
        $discussions = new \Tymy\Discussions($this->tapiAuthenticator, $this);
        $this->template->discussions = $discussions->setWithNew(true)->fetch();
        
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evMonths = $events->eventsMonthly;
        $this->template->events = $events->getData();
        $this->template->eventTypes = $this->getEventTypes();
        $usersArray = $this->getUsers()->data;
        usort($usersArray, array( $this, 'sortUsersByLastLogin' ));
        $this->template->users = $usersArray;
    }
    
    private static function sortUsersByLastLogin($a, $b){
        return strtotime($a->lastLogin) < strtotime($b->lastLogin) ? 1 : -1;
    }

}
