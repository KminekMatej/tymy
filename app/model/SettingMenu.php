<?php

namespace App\Model;

/**
 * Object setting accessible configuration area
 *
 * @author kminekmatej
 */
class SettingMenu {
    
    public $code;
    public $name;
    public $href;
    public $icon;
    public $enabled;
    
    public function __construct($code, $name, $href, $icon, $enabled) {
        $this->code = $code;
        $this->name = $name;
        $this->href = $href;
        $this->icon = $icon;
        $this->enabled = $enabled;
    }
    
}
