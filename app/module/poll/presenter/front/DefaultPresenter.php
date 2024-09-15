<?php

namespace Tymy\Module\Poll\Presenter\Front;

use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Manager\VoteManager;
use Tymy\Module\Poll\Model\Poll;

class DefaultPresenter extends SecuredPresenter
{
    #[\Nette\DI\Attributes\Inject]
    public PollManager $pollManager;

    #[\Nette\DI\Attributes\Inject]
    public VoteManager $voteManager;

    public function actionDefault(?string $resource = null): void
    {
        if ($resource) {
            $this->setView("poll");
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("poll.poll", 2), $this->link(":Poll:Default:"));
    }

    public function renderDefault(): void
    {
        $this->template->polls = $this->pollManager->getListUserAllowed();
    }

    public function renderPoll(?string $resource = null): void
    {
        $poll = $this->pollManager->getById($this->parseIdFromWebname($resource));
        assert($poll instanceof Poll);
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

    public function handleVote(int $pollId, ?int $userId = null): void
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
                "userId" => $userId ?: $this->user->getId(),    //make alien voting possible
                $opt["type"] => $opt["type"] == "numericValue" ? (float) $opt["value"] : $opt["value"]
            ];
        }
        $this->redrawControl("poll-results");
        $this->redrawNavbar();

        try {
            $this->voteManager->setPoll($poll)->create($votes, $pollId);
        } catch (TymyResponse $tResp) {
            $this->respondByTymyResponse($tResp);
        }
    }
}
