<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Discussion\Model\Discussion;

class DiscussionPresenter extends SettingBasePresenter
{
    public function actionDefault(?string $resource = null): void
    {
        if ($resource) {
            $this->setView("discussion");
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("discussion.discussion", 2), $this->link(":Setting:Discussion:"));
    }

    public function renderDefault(): void
    {
        $this->template->isNew = false;
        $discussions = $this->discussionManager->getList();
        $this->template->discussions = $discussions;
        $this->template->discussionsCount = count($discussions);
    }

    public function renderNew(): void
    {
        $this->allowPermission("DSSETUP");

        $this->addBreadcrumb($this->translator->translate("discussion.new"));

        $this->template->isNew = true;
        $this->template->discussion = (new Discussion())
            ->setId(-1)
            ->setCaption("")
                ->setDescription("")
                ->setPublicRead(true)
                ->setEditablePosts(true)
                ->setOrder(0);

        $this->setView("discussion");
    }

    public function renderDiscussion(?string $resource = null): void
    {
        $this->allowPermission("DSSETUP");

        //RENDERING DISCUSSION DETAIL
        $discussionObj = $this->discussionManager->getByWebName($resource);
        if ($discussionObj == null) {
            $this->flashMessage($this->translator->translate("discussion.errors.discussionNotExists", null, ['id' => $resource]), "danger");
            $this->redirect(':Setting:Event:');
        }
        $this->addBreadcrumb($discussionObj->getCaption(), $this->link(":Setting:Discussion:", $discussionObj->getWebName()));
        $this->template->isNew = false;
        $this->template->discussion = $discussionObj;
    }

    public function handleDiscussionsEdit(): void
    {
        $post = $this->getRequest()->getPost();
        $binders = $post["binders"];
        foreach ($binders as $bind) {
            $this->discussionManager->update($bind["changes"], $bind["id"]);
        }
    }

    public function handleDiscussionCreate(): void
    {
        $discussionData = (object) $this->getRequest()->getPost()["changes"]; // new discussion is always as ID 1
        $discussionData->publicRead = $discussionData->publicRead == "true" ?? false;
       $discussionData->editablePosts = $discussionData->editablePosts == "true" ?? false;
        $this->discussionManager->create((array) $discussionData);
        $this->redirect(':Setting:Discussion:');
    }

    public function handleDiscussionEdit(): void
    {
        $bind = $this->getRequest()->getPost();
        $this->discussionManager->update($bind["changes"], $bind["id"]);
    }

    public function handleDiscussionDelete(): void
    {
        $bind = $this->getRequest()->getPost();
        $this->discussionManager->delete($bind["id"]);
    }
}
