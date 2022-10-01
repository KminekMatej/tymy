<?php

namespace Tymy\Module\Team\Presenter\Front;

use Nette\Application\UI\Form;
use stdClass;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Row;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\InvitationManager;
use Tymy\Module\User\Model\Invitation;

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
        if (!$this->getUser()->isAllowed($this->user->getId(), Privilege::SYS('USR_CREATE'))) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"), "warning");
            $this->redirect(':Core:Default:');
        }

        $this->template->cols = [
            "Id",
            $this->translator->translate("settings.title"),
            $this->translator->translate("settings.description"),
            $this->translator->translate("settings.status"),
            $this->translator->translate("common.created"),
        ];

        $invitations = $this->invitationManager->getList();
        $this->template->rows = [];
        foreach ($invitations as $invitation) {
            /* @var $invitation Invitation */
            $row = new Row([
                $invitation->getId(),
                $invitation->getFirstName(),
                $invitation->getLastName(),
                $this->translator->translate("team.invitation-" . $invitation->getStatus()),
                $invitation->getCreated()->format(BaseModel::DATE_CZECH_FORMAT) . ", " . $this->userManager->getById($invitation->getCreatedUserId())->getDisplayName(),
            ]);
            switch ($invitation->getStatus()) {
                case Invitation::STATUS_ACCEPTED:
                    $row->addClass("text-success");
                    break;
                case Invitation::STATUS_EXPIRED:
                    $row->addClass("text-secondary")->setStyle("background-color: #ededed");
                    break;
            }

            $this->template->rows[] = $row;
        }

        $this->template->invitations = $invitations;
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
