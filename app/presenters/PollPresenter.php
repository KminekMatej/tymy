<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class PollPresenter extends SecuredPresenter {

    public $navbar;
    /** @var \Tymy\Poll @inject */
    public $poll;
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Ankety", "link" => $this->link("Poll:")]]);
    }
    
    public function renderDefault() {
        //todo
    }
    
    public function renderPoll($anketa) {
        $pollId = NULL;
        foreach ($this->polls->getData() as $p) {
            if($p->webName == $anketa){
                $pollId = $p->id;
                $this->setLevelCaptions(["2" => ["caption" => $p->caption, "link" => $this->link("Poll:poll", $p->webName) ] ]);
                break;
            }
        }
        
        $this->template->poll = $this->poll->recId($pollId)->getData();
        $this->template->users = $this->users->getData();
    }
    
    public function handleVote($pollId){
        $votes = [];
        $post = $this->getRequest()->getPost();
        foreach ($post as $optId => $opt) {
            if(array_key_exists("value", $opt)){
                $votes[] = ["optionId" => $optId, $opt["type"] => $opt["type"] == "numericValue" ? (int)$opt["value"] : $opt["value"] ];
            }
        }
        $this->redrawControl ("poll-results");
        $this->poll->recId($pollId)->vote($votes);
    }
}
