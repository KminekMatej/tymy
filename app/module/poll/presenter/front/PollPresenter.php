<?php

namespace Tymy\Module\Poll\Presenter\Front;

use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Manager\VoteManager;
use Tymy\Module\Poll\Model\Poll;

class PollPresenter extends SecuredPresenter
{

    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public VoteManager $voteManager;

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("poll.poll", 2), "link" => $this->link("Poll:")]]);
    }

    public function renderDefault($anketa)
    {
        /* @var $poll Poll */
        $poll = $this->pollManager->getById($this->parseIdFromWebname($anketa));
        $this->template->users = $this->userManager->getIdList();

        $this->setLevelCaptions(["2" => ["caption" => $poll->getCaption(), "link" => $this->link("Poll:poll", $poll->getWebName())]]);

        $this->template->poll = $poll;
        $this->template->radioLayout = $poll->getMinItems() == 1 && $poll->getMaxItems() == 1;

        $this->template->resultsDisplayed = $this->translator->translate("poll.resultsDisplayed");
        $this->template->resultsDisplayedAfterVote = $this->translator->translate("poll.resultsDisplayedAfterVote");
        $this->template->resultsToYouOnly = $this->translator->translate("poll.resultsToYouOnly");
        $this->template->resultsAreSecret = $this->translator->translate("poll.resultsAreSecret");
        $this->template->resultsDisplayedWhenClosed = $this->translator->translate("poll.resultsDisplayedWhenClosed");
    }

    public function handleVote($pollId)
    {
        $votes = [];
        $post = $this->getRequest()->getPost();
        foreach ($post as $optId => $opt) {
            if (!array_key_exists("value", $opt)) {
                continue;
            }

            $votes[] = [
                "optionId" => $optId,
                "userId" => $this->user->getId(),
                $opt["type"] => $opt["type"] == "numericValue" ? (int) $opt["value"] : $opt["value"]
            ];
        }
        $this->redrawControl("poll-results");
        $this->redrawNavbar();

        $this->voteManager->create($votes, $pollId);
    }

}