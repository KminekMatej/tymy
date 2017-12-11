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
                try {
        $poll = $this->poll
                ->reset()
                ->recId($this->parseIdFromWebname($anketa))
                ->getData();
        $this->template->users = $this->users->getResult();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }

        $this->setLevelCaptions(["2" => ["caption" => $poll->caption, "link" => $this->link("Poll:poll", $poll->webName) ] ]);
        
        $this->template->poll = $poll;
    }
    
    public function handleVote($pollId) {
        $votes = [];
        $post = $this->getRequest()->getPost();
        foreach ($post as $optId => $opt) {
            if (array_key_exists("value", $opt)) {
                $votes[] = ["optionId" => $optId, $opt["type"] => $opt["type"] == "numericValue" ? (int) $opt["value"] : $opt["value"]];
            }
        }
        $this->redrawControl("poll-results");
        try {
            $this->poll->recId($pollId)->vote($votes);
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
    }

}
