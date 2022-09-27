<?php

namespace Tymy\Module\Team\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\User\Manager\InvitationManager;

/**
 * Description of InvitationPresenter
 *
 * @author kminekmatej, 25. 9. 2022, 21:07:50
 */
class InvitationPresenter extends SecuredPresenter
{
    /** @inject */
    public InvitationManager $invitationManager;

    public function beforeRender()
    {
        parent::beforeRender();

        $this->addBreadcrumb($this->translator->translate("team.team", 1), $this->link(":Team:Default:"));
        $this->addBreadcrumb($this->translator->translate("team.invitation", 2), $this->link(":Team:Invitation:"));
    }

    public function renderDefault()
    {
        $this->template->invitations = $this->invitationManager->getList();
    }

    public function createComponentInvitationForm()
    {
        $form = new Form();

        $form->addText("firstName", $this->translator->translate("team.firstName"));
        $form->addText("lastName", $this->translator->translate("team.lastName"));
        $form->addText("email", $this->translator->translate("team.email"));

        $form->addSubmit("save");

        $form->onSuccess[] = [$this, ["invitationFormSuccess"]];

        return $form;
    }

    public function invitationFormSuccess(Form $form, stdClass $values)
    {
        $this->invitationManager->create((array) $values);

        $this->flashMessage($this->translator->translate("team.alerts.invitationCreated"), "success");
    }
}
