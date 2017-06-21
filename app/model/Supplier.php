<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

class Supplier {
    
    private $tym;
    
    function getTym() {
        return $this->tym;
    }

    function setTym($tym) {
        $this->tym = $tym;
    }

    public function __construct($tym) {
        $this->setTym($tym);
    }
}
