<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette\Security\SimpleIdentity;
use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\Sign\Form\SignUpFormFactory;
use Tymy\Module\User\Manager\InvitationManager;
use Tymy\Module\User\Model\Invitation;

class ByInvitePresenter extends BasePresenter
{
    /** @inject */
    public SignUpFormFactory $signUpFactory;

    /** @inject */
    public InvitationManager $invitationManager;

    public function renderDefault(string $invite)
    {
        $invitation = $this->invitationManager->getByCode($invite);

        if (!$invitation) {
            $this->flashMessage($this->translator->translate("team.invitation", 1) . " $invite " . $this->translator->translate("common.alerts.notFound", 2), "danger");
            $this->redirect(":Sign:In:");
        }

        if ($invitation->getStatus() == Invitation::STATUS_EXPIRED) { //already expired
            $this->flashMessage($this->translator->translate("team.errors.invitationExpired", 1) . " " . strtolower($this->translator->translate("team.invitation-expired")), "danger");
            $this->redirect(":Core:Default:");
        } elseif ($invitation->getStatus() == Invitation::STATUS_ACCEPTED) {
            $this->flashMessage($this->translator->translate("team.errors.invitationAccepted", 1) . " $invite " . $this->translator->translate("common.alerts.notFound", 2), "danger");
            $this->redirect(":Sign:In:");
        }

        $this->template->invitation = $invitation;
    }

    protected function createComponentSignUpForm()
    {
        $invitation = $this->invitationManager->getByCode($this->getRequest()->getParameter("invite"));

        return $this->signUpFactory->create(function (SimpleIdentity $registeredIdentity) {
                $this->flashMessage($this->translator->translate("common.alerts.registrationSuccesfull"), 'success');
                $this->user->setExpiration('20 minutes');
                $this->user->login($registeredIdentity);
                $this->redirect(':Core:Default:');
            }, $invitation);
    }
}
