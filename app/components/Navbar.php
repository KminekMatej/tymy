<?php

namespace Nette\Application\UI;

use Nette;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control {
    
    private $discussions;
    private $polls;
    private $events;
    private $user;
    private $presenter;
    
    
    public function __construct(Nette\Application\UI\Presenter $presenter) {
        parent::__construct();
        $this->discussions = new \Tymy\Discussions($presenter->tapiAuthenticator, $presenter);
        $this->polls = new \Tymy\Polls($presenter->tapiAuthenticator, $presenter);
        $this->events = new \Tymy\Events($presenter->tapiAuthenticator, $presenter);
        $this->user = $presenter->getUser();
        $this->presenter = $presenter;
    }
    
    private function discussions(){
        $discussions = $this->discussions
                ->setWithNew(true)
                ->fetch();
        $this->template->discussionWarnings = $this->discussions->getResult()->menuWarningCount;
        $this->template->discussions = (object)$discussions;
        
    }
    
    private function players(){
        $players = $this->presenter->getUsers(TRUE);
        $this->template->counts = $players->counts;
        $this->template->playersWarnings = $players->menuWarningCount;
        $this->template->me = $players->me;
    }
    
    private function polls(){
        $polls = $this->polls->fetch();
        $this->template->voteWarnings = $this->polls->getResult()->menuWarningCount;
        $this->template->polls = (object)$polls;
        
    }
    
    private function events(){
        $events = $this->events
                ->withMyAttendance(true)
                ->from(date("Ymd"))
                ->to(date("Ymd", strtotime(" + 1 month")))
                ->order("startTime")
                ->fetch();
        $this->template->eventWarnings = $this->events->getResult()->menuWarningCount;
        $this->template->events = (object)$events;
        
    }
    
    public function render(){
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/navbar.latte');
        $this->template->levels = $this->presenter->getLevelCaptions();
        $this->template->presenterName = $this->presenter->getName();
        $this->template->action = $this->presenter->getAction();
        $this->template->tym = $this->presenter->supplier->getTym();
        $this->template->userId = $this->user->getId();
        
        //render menus
        $this->discussions();
        //render players
        $this->players();
        //render events
        $this->events();
        //render polls
        $this->polls();

        $template->render();
    }
    
    public function handleRefresh() {
        if ($this->parent->isAjax()) {
            $this->redrawControl('nav');
        } else {
            $this->parent->redirect('this');
        }
    }
}
