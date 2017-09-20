<?php

namespace App\Presenters;

use Nette;
use App\Forms;

class SignPresenter extends BasePresenter {

    /** @var Forms\SignInFormFactory @inject */
    public $signInFactory;

    /** @var Forms\SignUpFormFactory @inject */
    public $signUpFactory;
    
    /** @var Forms\PwdLostFormFactory @inject */
    public $pwdLostFactory;
    
    /** @var Forms\PwdResetFormFactory @inject */
    public $pwdResetFactory;
    
    /** @var \App\Model\Supplier @inject */
    public $supplier;

    /** @var \Tymy\Logout @inject */
    public $logout;
    
    /** @var \Tymy\PwdLost @inject */
    public $pwdLost;
    
    /** @var \Tymy\PwdReset @inject */
    public $pwdReset;
    
    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        return $this->signInFactory->create(function () {
                    $this->redirect('Homepage:');
                }, $this->supplier);
    }

    /**
     * Sign-up form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignUpForm() {
        return $this->signUpFactory->create(function () {
                    $this->flashMessage('Succesfully registered. Now you need to wait for administrator approval.', 'success');
                    $this->redirect('Sign:In');
                });
    }
    
    /**
     * PWD Lost form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentPwdLostForm() {
        $form = $this->pwdLostFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, \stdClass $values) {
            $this->pwdLost
                    ->setCallbackUri($this->getHttpRequest()->getUrl()->getBaseUrl() . $this->link('Sign:pwdreset', ["code" => "%s"]))
                    ->setHostname($this->getHttpRequest()->getRemoteHost())
                    ->setMail($values->email)
                    ->getData();
            
            $this->flashMessage('Kód k resetování byl zaslán na Vaši e-mailovou adresu');

            $this->redirect('Sign:pwdreset');
        };
        return $form;
    }
    
    /**
     * PWD Reset form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentPwdResetForm() {
        $form = $this->pwdResetFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, \stdClass $values) {
            $data = $this->resetPwd($values->code);
            $this->flashMessage('Vaše heslo bylo úspěšně resetováno');
            $this->redirect('Sign:pwdnew', ["pwd" => $data]);
        };
        return $form;
    }
    
    public function actionOut() {
        if (!is_null($this->getUser()->getIdentity())) {
            $this->logout->logout();
            $this->getUser()->logout();
        }
        $this->flashMessage('You have been succesfully signed out');
        $this->redirect('Sign:In');
    }
    
    public function renderPwdNew() {
        $this->template->pwdNew = $this->getRequest()->getParameter("pwd");
    }
    
    public function renderPwdReset() {
        if(($resetCode = $this->getRequest()->getParameter("code")) != null){
            $data = $this->resetPwd($resetCode);
            $this->flashMessage('Vaše heslo bylo úspěšně resetováno');
            $this->redirect('Sign:pwdnew', ["pwd" => $data]);
        }
    }
    
    private function resetPwd($code){
        return $this->pwdReset
                    ->setCode($code)
                    ->getData();
    }

}
