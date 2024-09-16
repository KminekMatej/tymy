<?php

namespace Tymy\Module\Team\Presenter\Front;

use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use stdClass;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\User\Manager\InvitationManager;
use Tymy\Module\User\Model\User;

/**
 * Description of InvitationPresenter
 */
class InvitationPresenter extends SecuredPresenter
{
    #[\Nette\DI\Attributes\Inject]
    public InvitationManager $invitationManager;

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->addBreadcrumb($this->translator->translate("team.team", 1), $this->link(":Team:Default:"));
        $this->addBreadcrumb($this->translator->translate("team.invitation", 2), $this->link(":Team:Invitation:"));

        assert($this->template instanceof Template);
        $this->template->addFilter("loadUser", fn(int $userId): ?User => $this->userManager->getById($userId));
    }

    public function renderDefault(): void
    {
        if (!$this->getUser()->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect(':Core:Default:');
        }

        $this->template->invitations = $this->invitationManager->getList();
        $this->template->trans = $this->translator;
    }

    public function handleDelete(int $id): void
    {
        if (!$this->getUser()->isAllowed((string) $this->user->getId(), "SYS:USR_CREATE")) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect(':Core:Default:');
        }

        $this->invitationManager->delete($id);
        $this->redirect(":Team:Invitation:");
    }

    public function createComponentInvitationForm(): Form
    {
        $form = new Form();

        $form->addText("firstName", $this->translator->translate("team.firstName"));
        $form->addText("lastName", $this->translator->translate("team.lastName"));
        $form->addEmail("email", $this->translator->translate("team.email"))->addRule($form::EMAIL)
            ->addRule($form::IS_NOT_IN, $this->translator->translate("team.alerts.emailExists"), $this->userManager->getExistingEmails())
            ->addRule($form::IS_NOT_IN, $this->translator->translate("team.alerts.emailExists"), $this->invitationManager->getExistingEmails());

        $form->addSubmit("save");

        $form->onSuccess[] = fn(Form $form, \stdClass $values) => $this->invitationFormSuccess($form, $values);

        return $form;
    }

    public function invitationFormSuccess(Form $form, stdClass $values): void
    {
        $this->invitationManager->create((array) $values);

        $this->flashMessage($this->translator->translate("team.alerts.invitationCreated"), "success");
    }
}
