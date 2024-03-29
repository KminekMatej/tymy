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

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("discussion.discussion", 2), $this->link(":Discussion:Default:"));
    }

    public function renderDefault(): void
    {
        $this->template->discussions = $this->discussionManager->getListUserAllowed($this->user->getId());
    }
}
