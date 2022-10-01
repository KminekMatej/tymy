<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\User\Manager\InvitationManager;

class ByInvitePresenter extends BasePresenter
{

    /** @inject */
    public InvitationManager $invitationManager;
    
    public function renderDefault(string $invite)
    {
        $invitation = $this->invitationManager->getByCode($invite);

        if (!$invitation) {
            $this->flashMessage($this->translator->translate("team.invitation", 1) . " $invite " . $this->translator->translate("common.alerts.notFound", 2), "danger");
            $this->redirect(":Sign:In:");
        }

        $this->template->invitation = $invitation;
    }
}
