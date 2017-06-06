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
        $polls = new \Tymy\Polls($this->tapiAuthenticator, $this);
        $pollId = NULL;
        foreach ($polls->fetch() as $p) {
            if(Strings::webalize($p->caption) == $anketa){
                $pollId = $p->id;
                $this->setLevelCaptions(["1" => ["caption" => $p->caption, "link" => $this->link("Poll:poll", Strings::webalize($p->caption)) ] ]);
                break;
            }
        }
        
        $pollObj = new \Tymy\Poll($this->tapiAuthenticator, $this);
        $pollData = $pollObj->
                recId($pollId)->
                fetch();
        
        $this->template->poll = $pollData;
        $this->template->users = $this->getUsers();
    }
    
    public function handleVote($pollId){
        $votes = [];
        $post = $this->getRequest()->getPost();
        foreach ($post as $optId => $opt) {
            if(array_key_exists("value", $opt)){
                $votes[] = ["optionId" => $optId, $opt["type"] => $opt["type"] == "numericValue" ? (int)$opt["value"] : $opt["value"] ];
            }
        }
        
        $poll = new \Tymy\Poll($this->tapiAuthenticator, $this);
        $poll->recId($pollId)
            ->vote($votes);
    }
}
