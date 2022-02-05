<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Discussion\Model\Discussion;

class DiscussionPresenter extends SettingBasePresenter
{
    public function actionDefault(?string $resource = null)
    {
        if ($resource) {
            $this->setView("discussion");
        }
    }

    public function renderDefault()
    {
        $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link(":Setting:Discussion:")]]);
        $this->template->isNew = false;
        $discussions = $this->discussionManager->getList();
        $this->template->discussions = $discussions;
        $this->template->discussionsCount = count($discussions);
    }

    public function renderNew()
    {
        $this->allowPermission("DSSETUP");

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link(":Setting:Discussion:")],
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

    public function renderDiscussion(?string $resource = null)
    {
        $this->allowPermission("DSSETUP");

        //RENDERING DISCUSSION DETAIL
        $discussionObj = $this->discussionManager->getByWebName($resource);
        if ($discussionObj == null) {
            $this->flashMessage($this->translator->translate("discussion.errors.discussionNotExists", null, ['id' => $discussionId]), "danger");
            $this->redirect(':Setting:Event:');
        }
        $this->setLevelCaptions(["3" => ["caption" => $discussionObj->getCaption(), "link" => $this->link(":Setting:Discussion:", $discussionObj->getWebName())]]);
        $this->template->isNew = false;
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
        $this->redirect(':Setting:Discussion:');
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
