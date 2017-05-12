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
    private $userId;
    private $sessionSection;
    
    
    public function __construct($userId, \Nette\Http\SessionSection $sessionSection) {
        parent::__construct();
        $this->sessionSection = $sessionSection;
        $this->userId = $userId;
    }
    
    public function render($event){
        
        $this->template->addFilter("prestatusClass", function ($myPreStatus, $myPostStatus, $btn, $startTime) {
            switch ($btn) {
                case "LAT": // Late
                    $color = "warning";
                    break;
                case "NO":
                    $color = "danger";
                    break;
                case "YES":
                    $color = "success";
                    break;
                case "DKY": // Dont Know Yet
                    $color = "warning";
                    break;
                default:
                    $color = "primary";
                    break;
            }
            
            if(strtotime($startTime) > strtotime(date("c")))// pokud podminka plati, akce je budouci
                return $btn == $myPreStatus ? "btn-outline-$color active" : "btn-outline-$color";
            else if($myPostStatus == "not-set") // akce uz byla, post status nevyplnen
                return $btn == $myPreStatus && $myPreStatus != "not-set" ? "btn-outline-$color disabled active" : "btn-outline-secondary disabled";
            else 
                return $btn == $myPostStatus && $myPostStatus != "not-set" ? "btn-outline-$color disabled active" : "btn-outline-secondary disabled";
        });
        
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/attendancerow.latte');
        $this->template->eventTypes = $this->sessionSection["eventTypes"];
        $this->template->ev = $event;
        $this->template->userId = $this->userId;

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
