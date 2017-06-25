<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class User extends UserInterface{
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        $this->fullUrl .= "user/" .$this->recId;
        return $this;
    }
    
    protected function postProcess(){
        $data = $this->getData();
        $data->webName = \Nette\Utils\Strings::webalize($data->fullName);
        if(!property_exists($data, "gender")) $data->gender = "UNKNOWN"; //set default value
        $this->timezone($data->lastLogin);
        $this->userWarnings($data);
        $this->userPermissions($data);
    }

}
