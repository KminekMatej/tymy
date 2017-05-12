<?php

namespace Nette\Application\UI;

use Nette;
use Nette\Utils\Strings;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AttendanceRow
 *
 * @author matej
 */
class AttendanceRow extends Control{
    
    private $user;
    private $presenter;
    private $event;
    
    public function __construct(Nette\Application\UI\Presenter $presenter, \Tymy\Event $event) {
        parent::__construct();
        $this->user = $presenter->getUser();
        $this->presenter = $presenter;
        $this->event = $event;
    }
    
    public function render(){
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/attendancerow.latte');
        
        $sessionSection = $this->getSession()->getSection("tymy");
        
        $this->template->eventTypes = $sessionSection["eventTypes"];
        $this->template->ev = $this->event;
        $this->template->userId = $this->user->getId();

        $template->render();
    }
    
    public function handleAttendance() {
        //TODO copied src
        if ($this->parent->isAjax()) {
            $this->redrawControl('nav');
        } else {
            // redirect může jen presenter, nikoliv komponenta
            $this->parent->redirect('this');
        }
    }
    
}
