<?php

namespace Tymy\Module\Discussion\Presenter\Front;

use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Discussion\Manager\PostManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DefaultPresenter
 *
 * @author matej
 */
class DefaultPresenter extends SecuredPresenter
{

    /** @inject */
    public DiscussionManager $discussionManager;

    /** @inject */
    public PostManager $postManager;

    /** @inject */
    public UserManager $userManager;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link("Discussion:")]]);
    }

    public function renderDefault()
    {
        $this->template->discussions = $this->discussionManager->getListUserAllowed($this->user->getId());
    }

}