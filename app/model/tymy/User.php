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
    
    public function __construct(Nette\Application\UI\Presenter $presenter) {
        parent::__construct($presenter);
    }
    
    public function select() {
        if (!isset($this->recId))
            throw new TymyException('User ID not set!');
        $this->fullUrl .= "user/" .$this->recId;
        return $this;
    }
    
    public function fetch() {
        $player = parent::fetch();
        $player->webName = Strings::webalize($player->fullName);
        $this->checkPlayerData($player);
        return $player;
    }
    
    protected function tzFields($jsonObj){
        $this->timezone($jsonObj->lastLogin);
    }

}
