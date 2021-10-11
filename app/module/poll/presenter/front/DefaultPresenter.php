<?php

namespace Tymy\Module\Poll\Presenter\Front;

use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Poll\Manager\PollManager;

class DefaultPresenter extends SecuredPresenter
{

    /** @inject */
    public PollManager $pollManager;

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("poll.poll", 2), "link" => $this->link(":Poll:Default:")]]);
    }

    public function renderDefault()
    {
        $this->template->polls = $this->pollManager->getListMenu();
    }

}