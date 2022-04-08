<?php

namespace Tymy\Module\Poll\Presenter\Front;

use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Manager\VoteManager;
use Tymy\Module\Poll\Model\Poll;

class DefaultPresenter extends SecuredPresenter
{
    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public VoteManager $voteManager;

    public function actionDefault(?string $resource = null)
    {
        if ($resource) {
            $this->setView("poll");
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("poll.poll", 2), $this->link(":Poll:Default:"));
    }

    public function renderDefault()
    {
        $this->template->polls = $this->pollManager->getListMenu();
    }

    public function renderPoll(?string $resource = null)
    {
        /* @var $poll Poll */
        $poll = $this->pollManager->getById($this->parseIdFromWebname($resource));
        $this->template->users = $this->userManager->getIdList();

        $this->addBreadcrumb($poll->getCaption(), $this->link(":Poll:Default:", $poll->getWebName()));

        $this->template->poll = $poll;
        $this->template->radioLayout = $poll->getMinItems() == 1 && $poll->getMaxItems() == 1;

        $this->template->resultsDisplayed = $this->translator->translate("poll.resultsDisplayed");
        $this->template->resultsDisplayedAfterVote = $this->translator->translate("poll.resultsDisplayedAfterVote");
        $this->template->resultsToYouOnly = $this->translator->translate("poll.resultsToYouOnly");
        $this->template->resultsAreSecret = $this->translator->translate("poll.resultsAreSecret");
        $this->template->resultsDisplayedWhenClosed = $this->translator->translate("poll.resultsDisplayedWhenClosed");
    }

    public function handleVote(int $pollId): void
    {
        $votes = [];
        $post = $this->getRequest()->getPost();
        $poll = $this->pollManager->getById($pollId);
        
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
        \Tracy\Debugger::barDump($votes);
        $this->voteManager->setPoll($poll)->create($votes, $pollId);
    }
}
