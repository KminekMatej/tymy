<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Users extends Tymy{
    
    private $withErrors = FALSE;
    private $userType;
    
    public function __construct(Nette\Application\UI\Presenter $presenter, $userType = NULL) {
        parent::__construct($presenter);
        $this->userType = $userType;
    }
    
    public function select() {
        $this->fullUrl .= "users/";
        if(!is_null($this->userType))
            $this->fullUrl .= "status/" . $this->userType . "/";
        return $this;
    }
        
    public function fetch() {
        $players = parent::fetch();
        foreach ($players as $player) {
            $player->webName = Strings::webalize($player->fullName);
            $this->checkPlayerData($player);
        }
        return $players;
    }

    protected function tzFields($jsonObj){
        foreach ($jsonObj as $user) {
            $this->timezone($user->lastLogin);
        }
    }
}
