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
        $this->template->users = $this->getUsers()->data;
    }

}
