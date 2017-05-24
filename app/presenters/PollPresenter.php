<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class PollPresenter extends SecuredPresenter {

    public $navbar;
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["0" => ["caption" => "Ankety", "link" => $this->link("Poll:")]]);
    }
    
    public function renderDefault() {
        //todo
    }
    
    public function renderPoll($anketa) {
        $polls = new \Tymy\Polls($this);
        $pollId = NULL;
        foreach ($polls->fetch() as $p) {
            if(Strings::webalize($p->caption) == $anketa){
                $pollId = $p->id;
                $this->setLevelCaptions(["1" => ["caption" => $p->caption, "link" => $this->link("Poll:poll", Strings::webalize($p->caption)) ] ]);
                break;
            }
        }
        
        $pollObj = new \Tymy\Poll($this);
        $pollData = $pollObj->
                recId($pollId)->
                fetch();
        
        $this->template->poll = $pollData;
    }
}
