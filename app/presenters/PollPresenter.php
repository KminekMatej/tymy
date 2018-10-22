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
    
    /** @var PollListResource @inject */
    public $pollList;
    
    
    /** @var PollDetailResource @inject */
    public $poll;
    
    /** @var PollVoteResource @inject */
    public $pollVoter;
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Ankety", "link" => $this->link("Poll:")]]);
    }
    
    public function renderDefault() {
        parent::showNotes();
        try {
            $this->template->polls = $this->pollList->init()->setMenu(TRUE)->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    public function renderPoll($anketa) {
                try {
        $poll = $this->poll->init()
                ->setId($this->parseIdFromWebname($anketa))
                ->getData();
        $this->userList->init()->getData();
        $this->template->users = $this->userList->getById();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        parent::showNotes($poll->id);
        $this->setLevelCaptions(["2" => ["caption" => $poll->caption, "link" => $this->link("Poll:poll", $poll->webName) ] ]);
        
        $this->template->poll = $poll;
        
        $this->template->resultsDisplayed = $this->translator->translate("poll.resultsDisplayed");
        $this->template->resultsDisplayedAfterVote = $this->translator->translate("poll.resultsDisplayedAfterVote");
        $this->template->resultsToYouOnly = $this->translator->translate("poll.resultsToYouOnly");
        $this->template->resultsAreSecret = $this->translator->translate("poll.resultsAreSecret");
        $this->template->resultsDisplayedWhenClosed = $this->translator->translate("poll.resultsDisplayedWhenClosed");

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
            $this->pollVoter->init()->setId($pollId)->setVotes($votes)->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
    }

}
