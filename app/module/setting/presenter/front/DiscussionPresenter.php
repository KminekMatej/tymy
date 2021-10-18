<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Discussion\Model\Discussion;

class DiscussionPresenter extends SettingDefaultPresenter
{

    public function actionDefault($discussion = NULL)
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link(":Setting:discussions")]]);
        if (!is_null($discussion)) {
            $this->setView("discussion");
        } else {
            $this->template->isNew = false;
            $discussions = $this->discussionManager->getList();
            $this->template->discussions = $discussions;
            $this->template->discussionsCount = count($discussions);
        }
    }

    public function renderNew()
    {
        $this->allowSys("DSSETUP");

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link(":Setting:discussions")],
            "3" => ["caption" => $this->translator->translate("discussion.new")]
        ]);
        $this->template->isNew = true;
        $this->template->discussion = (new Discussion())
                ->setId(-1)
                ->setCaption("")
                ->setDescription("")
                ->setPublicRead("YES")
                ->setEditablePosts("YES")
                ->setOrder(0);

        $this->setView("discussion");
    }

    public function renderDiscussion($discussion)
    {
        $this->allowSys("DSSETUP");

        //RENDERING DISCUSSION DETAIL
        $discussionObj = $this->discussionManager->getByWebName($discussion);
        if ($discussionObj == NULL) {
            $this->flashMessage($this->translator->translate("discussion.errors.discussionNotExists", NULL, ['id' => $discussionId]), "danger");
            $this->redirect('Settings:events');
        }
        $this->setLevelCaptions(["3" => ["caption" => $discussionObj->getCaption(), "link" => $this->link(":Setting:discussions", $discussionObj->getWebName())]]);
        $this->template->isNew = FALSE;
        $this->template->discussion = $discussionObj;
    }

    public function handleDiscussionsEdit()
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->discussionManager->update($bind["changes"], $bind["id"]);
        }
    }

    public function handleDiscussionCreate()
    {
        $discussionData = (object) $this->getRequest()->getPost()["changes"]; // new discussion is always as ID 1
        $this->discussionManager->create($discussionData);
        $this->redirect('Settings:discussions');
    }

    public function handleDiscussionEdit()
    {
        $bind = $this->getRequest()->getPost();
        $this->discussionManager->update($bind["changes"], $bind["id"]);
    }

    public function handleDiscussionDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->discussionManager->delete($bind["id"]);
    }

}