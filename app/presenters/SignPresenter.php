<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use Tapi\LogoutResource;
use Tapi\PasswordLostResource;
use Tapi\PasswordResetResource;

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

    /** @var LogoutResource @inject */
    public $logout;
    
    /** @var PasswordLostResource @inject */
    public $pwdLost;
    
    /** @var PasswordResetResource @inject */
    public $pwdReset;
    
    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = $this->signInFactory->create(function (Nette\Application\UI\Form $form, $values) {
            try {
                $this->user->setExpiration('20 minutes');
                $this->user->login($values->name, $values->password);
            } catch (\Tymy\Exception\APIException $exc) {
                switch ($exc->getMessage()) {
                    case "Login not approved":
                        $this->flashMessage('Tento uživatel zatím nemá povolené přihlášení', "danger");
                        break;
                    default:
                        $this->flashMessage('Přihlášení bylo neúspěšné', "danger");
                        break;
                }
            }
            $this->redirect('Homepage:');
        });

        return $form;
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
            try {
                $this->pwdLost
                        ->setCallbackUri($this->getHttpRequest()->getUrl()->getBaseUrl() . $this->link('Sign:pwdreset', ["code" => "%s"]))
                        ->setHostname($this->getHttpRequest()->getRemoteHost())
                        ->setMail($values->email)
                        ->getData();    
            } catch (\Tymy\Exception\APIException $ex) {
                $this->flashMessage('Uživatel nebyl nalezen, je zablokován nebo došlo k chybě. Zkuste to znovu, nebo kontaktujte týmového administrátora.');
                $this->redirect('Sign:pwdlost');
            }
            
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
            $this->logout->perform();
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
    
    public function renderIn(){
        $this->template->multiple = $this->supplier->getTapi_config()["multiple_team"];
    }
    
    private function resetPwd($code){
        return $this->pwdReset
                    ->setCode($code)
                    ->getData();
    }

}
