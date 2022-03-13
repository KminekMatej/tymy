<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;
use Tymy\Module\Poll\Model\Option;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class PollPresenter extends SettingBasePresenter
{
    /** @inject */
    public PollManager $pollManager;

    /** @inject */
    public OptionManager $optionManager;

    public function actionDefault(?string $resource = null)
    {
        if ($resource) {
            $this->setView("poll");
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("poll.poll", 2), $this->link(":Setting:Poll:"));
    }

    public function renderDefault()
    {
        $this->template->polls = $this->pollManager->getList();
    }

    public function renderNew()
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        $this->addBreadcrumb($this->translator->translate("poll.new"));

        $this->template->polls = [(new Poll())
                ->setId(-1)
                ->setCaption("")
                ->setDescription("")
                ->setStatus("DESIGN")
                ->setMinItems(1)
                ->setMaxItems(99)
                ->setMainMenu("")
                ->setAnonymousResults("")
                ->setChangeableVotes("")
                ->setShowResults("NEVER")
        ];
    }

    public function renderPoll(?string $resource = null)
    {
        $this->allowPermission('ASK.VOTE_UPDATE');

        //RENDERING POLL DETAIL
        $pollId = $this->parseIdFromWebname($resource);
        /* @var $pollObj Poll */
        $pollObj = $this->pollManager->getById($pollId);
        if ($pollObj == null) {
            $this->flashMessage($this->translator->translate("poll.errors.pollNotExists", null, ['id' => $pollId]), "danger");
            $this->redirect(':Setting:Poll:');
        }
        if (count($pollObj->getOptions()) == 0) {
            $pollObj->setOptions([(new Option())->setId(-1)->setPollId($pollId)->setCaption("")->setType("TEXT")]);
        }
        $this->addBreadcrumb($pollObj->getCaption(), $this->link(":Setting:Poll:", $pollObj->getWebName()));
        $this->template->poll = $pollObj;
    }

    public function handlePollsEdit()
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->pollManager->update($bind["changes"], $bind["id"]);
        }
    }

    public function handlePollCreate()
    {
        $this->pollManager->create($this->getRequest()->getPost()["changes"]);
        $this->redirect(':Setting:Poll:');
    }

    public function handlePollEdit()
    {
        $bind = $this->getRequest()->getPost();
        $this->pollManager->update($bind["changes"], $bind["id"]);
    }

    public function handlePollDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->pollManager->delete($bind["id"]);
    }

    public function handlePollOptionsEdit($poll)
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        $pollId = $this->parseIdFromWebname($poll);
        foreach ($binders as $bind) {
            $bind["changes"]["pollId"] = $pollId;
            $this->editPollOption($bind);
        }
    }

    public function handlePollOptionCreate($poll)
    {
        $pollData = $this->getRequest()->getPost()[1]; // new poll option is always as item 1
        $pollId = $this->parseIdFromWebname($poll);
        $pollData["pollId"] = $pollId;
        $this->optionManager->create($pollData);
    }

    public function handlePollOptionEdit($poll)
    {
        $bind = $this->getRequest()->getPost();
        $bind["changes"]["pollId"] = $this->parseIdFromWebname($poll);
        $this->editPollOption($bind);
    }

    public function handlePollOptionDelete($poll)
    {
        $bind = $this->getRequest()->getPost();
        $bind["pollId"] = $this->parseIdFromWebname($poll);
        $this->optionManager->delete($bind["pollId"], $bind["id"]);
    }

    /**
     * Update or create poll option
     *
     * @param array $bind
     * @return Option Created / updated option
     */
    private function editPollOption($bind): Option
    {
        if ($bind["id"] == -1) {
            return $this->optionManager->create($bind["changes"]);
        } else {
            return $this->optionManager->update($bind["changes"], $bind["id"]);
        }
    }
}
