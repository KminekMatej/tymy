<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette;
use stdClass;
use Tapi\Exception\APIException;
use Tymy\App\Forms\PwdLostFormFactory;
use Tymy\Module\Core\Presenter\Front\BasePresenter;

class PwdPresenter extends BasePresenter
{

    /** @inject */
    public PwdLostFormFactory $pwdLostFactory;

    public function renderReset()
    {
        if (($resetCode = $this->getRequest()->getParameter("code")) != null) {
            $data = $this->resetPwd($resetCode);
            $this->flashMessage($this->translator->translate("common.alerts.pwdResetSuccesfull"));
            $this->redirect(':Sign:Pwd:new', ["pwd" => $data]);
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
                $this->pwdLost->init()
                        ->setCallbackUri($this->link('//:Sign:Pwd:reset') . "?code=%2s")
                        ->setHostname($this->getHttpRequest()->getRemoteHost())
                        ->setMail($values->email)
                        ->getData();
            } catch (APIException $ex) {
                $this->flashMessage($this->translator->translate("common.alerts.userNotFound"));
                $this->redirect(':Sign:Pwd:lost');
            }

            $this->flashMessage($this->translator->translate("common.alerts.resetCodeSent"));

            $this->redirect(':Sign:Pwd:reset');
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
            $data = $this->resetPwd($values->code);
            $this->flashMessage($this->translator->translate("common.alerts.resetCodeSent"));
            $this->redirect(':Sign:Pwd:new', ["pwd" => $data]);
        };
        return $form;
    }

    private function resetPwd($code)
    {
        return $this->pwdReset->init()
                        ->setCode($code)
                        ->getData();
    }

}