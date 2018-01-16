<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;
use Tapi\PollDetailResource;
use Tapi\PollListResource;
use Tapi\PollVoteResource;
use Tapi\Exception\APIException;

class PollPresenter extends SecuredPresenter {

    public $navbar;
    
    /** @var PollDetailResource @inject */
    public $poll;
    
    /** @var PollVoteResource @inject */
    public $pollVoter;
    
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
                ->setId($this->parseIdFromWebname($anketa))
                ->getData();
        $this->userList->init()->getData();
        $this->template->users = $this->userList->getById();
        } catch (APIException $ex) {
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
            $this->pollVoter->setId($pollId)->setVotes($votes)->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
    }

}
