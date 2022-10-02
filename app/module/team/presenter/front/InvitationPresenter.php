<?php

namespace Tymy\Module\Team\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\InvitationManager;
use Tymy\Module\User\Model\User;

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

        $this->template->addFilter("loadUser", function (int $userId) {
            /* @var $user User */
            return $this->userManager->getById($userId);
        });
    }

    public function renderDefault()
    {
        if (!$this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('USR_CREATE'))) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect(':Core:Default:');
        }

        $this->template->invitations = $this->invitationManager->getList();
        $this->template->trans = $this->translator;
    }

    public function handleDelete(int $id)
    {
        if (!$this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('USR_CREATE'))) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect(':Core:Default:');
        }

        $this->invitationManager->delete($id);
        $this->redirect(":Team:Invitation:");
    }

    public function createComponentInvitationForm()
    {
        $form = new Form();

        $form->addText("firstName", $this->translator->translate("team.firstName"));
        $form->addText("lastName", $this->translator->translate("team.lastName"));
        $form->addEmail("email", $this->translator->translate("team.email"))->addRule($form::EMAIL);

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
