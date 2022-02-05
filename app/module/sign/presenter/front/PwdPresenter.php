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

    public function renderReset()
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

    public function renderNew()
    {
        $this->template->pwdNew = $this->getRequest()->getParameter("pwd");
    }

    /**
     * PWD Lost form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentPwdLostForm()
    {
        $form = $this->pwdLostFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, stdClass $values) {
            try {
                $this->userManager->pwdLost($values->email, $this->getHttpRequest()->getRemoteHost(), $this->link('//:Sign:Pwd:reset') . "?code=%2s");
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
     * @return Nette\Application\UI\Form
     */
    protected function createComponentPwdResetForm()
    {
        $form = $this->pwdResetFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, stdClass $values) {
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
