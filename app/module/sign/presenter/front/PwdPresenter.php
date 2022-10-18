<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette;
use stdClass;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\Sign\Form\PwdLostFormFactory;
use Tymy\Module\Sign\Form\PwdResetFormFactory;
use Tymy\Module\User\Manager\UserManager;

class PwdPresenter extends BasePresenter
{
    /** @inject */
    public PwdLostFormFactory $pwdLostFactory;

    /** @inject */
    public PwdResetFormFactory $pwdResetFactory;

    /** @inject */
    public UserManager $userManager;

    public function renderReset(): void
    {
        $resetCode = $this->getRequest()->getParameter("code");
        if (!empty($resetCode)) {
            try {
                $newPassword = $this->userManager->pwdReset($resetCode);
                $this->flashMessage($this->translator->translate("common.alerts.pwdResetSuccesfull"));
                $this->redirect(':Sign:Pwd:new', ["pwd" => $newPassword]);
            } catch (TymyResponse $tResp) {
                $this->handleTymyResponse($tResp);
            }
        }
    }

    public function renderNew(): void
    {
        $this->template->pwdNew = $this->getRequest()->getParameter("pwd");
    }

    /**
     * PWD Lost form factory.
     */
    protected function createComponentPwdLostForm(): \Nette\Application\UI\Form
    {
        $form = $this->pwdLostFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, stdClass $values): void {
            try {
                $this->userManager->pwdLost(trim($values->email), $this->getHttpRequest()->getRemoteHost(), $this->link('//:Sign:Pwd:reset') . "?code=%2s");
                $this->flashMessage($this->translator->translate("common.alerts.resetCodeSent"));
                $this->redirect(':Sign:Pwd:reset');
            } catch (TymyResponse $tResp) {
                $this->handleTymyResponse($tResp);
            }
        };
        return $form;
    }

    /**
     * PWD Reset form factory.
     */
    protected function createComponentPwdResetForm(): \Nette\Application\UI\Form
    {
        $form = $this->pwdResetFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, stdClass $values): void {
            try {
                $newPassword = $this->userManager->pwdReset($values->code);
                $this->flashMessage($this->translator->translate("common.alerts.pwdResetSuccesfull"));
                $this->redirect(':Sign:Pwd:new', ["pwd" => $newPassword]);
            } catch (TymyResponse $tResp) {
                $this->handleTymyResponse($tResp);
            }
        };
        return $form;
    }
}
