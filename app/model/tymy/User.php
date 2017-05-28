<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class User extends Tymy{
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        $this->fullUrl .= "user/" .$this->recId;
        return $this;
    }
    
    public function fetch() {
        $player = parent::fetch();
        $this->checkPlayerData($player);
        return $player;
    }
    
    protected function postProcess(){
        $data = $this->getData();
        $data->webName = \Nette\Utils\Strings::webalize($data->fullName);
        $this->timezone($data->lastLogin);
    }

}
