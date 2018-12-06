<?php

namespace App\Presenters;

use App\Forms\PwdLostFormFactory;
use App\Forms\PwdResetFormFactory;
use App\Forms\SignInFormFactory;
use App\Forms\SignUpFormFactory;
use App\Model\Supplier;
use App\Model\TapiAuthenticator;
use Nette;
use Nette\Application\UI\Form;
use stdClass;
use Tapi\Exception\APIException;
use Tapi\IsResource;
use Tapi\LogoutResource;
use Tapi\PasswordLostResource;
use Tapi\PasswordResetResource;
use Tapi\TapiService;
use Tracy\Debugger;


class SignPresenter extends BasePresenter {

    /** @var SignInFormFactory @inject */
    public $signInFactory;

    /** @var SignUpFormFactory @inject */
    public $signUpFactory;
    
    /** @var PwdLostFormFactory @inject */
    public $pwdLostFactory;
    
    /** @var PwdResetFormFactory @inject */
    public $pwdResetFactory;
    
    /** @var Supplier @inject */
    public $supplier;

    /** @var LogoutResource @inject */
    public $logout;
    
    /** @var PasswordLostResource @inject */
    public $pwdLost;
    
    /** @var PasswordResetResource @inject */
    public $pwdReset;
    
    /** @var TapiAuthenticator @inject */
    public $tapiAuthenticator;
    
    /** @var TapiService @inject */
    public $tapiService;
    
    /** @var IsResource @inject */
    public $is;
    
    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = $this->signInFactory->create(function (Form $form, $values) {
            try {
                $this->tapiAuthenticator->setTapiService($this->tapiService);
                $this->user->setExpiration('20 minutes');
                $r = $this->user->login($values->name, $values->password);
            } catch (APIException $exc) {
                switch ($exc->getMessage()) {
                    case "Login not approved":
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotApproved"), "danger");
                        break;
                    default:
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotSuccesfull") . ' (' . $exc->getMessage() . ")", "danger");
                        break;
                }
            }
            if(!is_null($this->user->getIdentity()))
                Debugger::log($this->user->getIdentity()->data["callName"] . "@" . $this->supplier->getTym () . " logged in");
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
                    $this->flashMessage($this->translator->translate("common.alerts.registrationSuccesfull"), 'success');
                    $this->redirect('Sign:In');
                });
    }
    
    /**
     * PWD Lost form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentPwdLostForm() {
        $form = $this->pwdLostFactory->create();
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, stdClass $values) {
            try {
                $this->pwdLost->init()
                        ->setCallbackUri($this->getHttpRequest()->getUrl()->getBaseUrl() . $this->link('Sign:pwdreset', ["code" => "%s"]))
                        ->setHostname($this->getHttpRequest()->getRemoteHost())
                        ->setMail($values->email)
                        ->getData();    
            } catch (APIException $ex) {
                $this->flashMessage($this->translator->translate("common.alerts.userNotFound"));
                $this->redirect('Sign:pwdlost');
            }
            
            $this->flashMessage($this->translator->translate("common.alerts.resetCodeSent"));

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
        $form->onSuccess[] = function (Nette\Application\UI\Form $form, stdClass $values) {
            $data = $this->resetPwd($values->code);
            $this->flashMessage($this->translator->translate("common.alerts.resetCodeSent"));
            $this->redirect('Sign:pwdnew', ["pwd" => $data]);
        };
        return $form;
    }
    
    public function actionOut() {
        if (!is_null($this->getUser()->getIdentity())) {
            $this->logout->init()->perform();
            $this->getUser()->logout();
        }
        $this->flashMessage($this->translator->translate("common.alerts.logoutSuccesfull"));
        $this->redirect('Sign:In');
    }
    
    public function renderPwdNew() {
        $this->template->pwdNew = $this->getRequest()->getParameter("pwd");
    }
    
    public function renderPwdReset() {
        if(($resetCode = $this->getRequest()->getParameter("code")) != null){
            $data = $this->resetPwd($resetCode);
            $this->flashMessage($this->translator->translate("common.alerts.pwdResetSuccesfull"));
            $this->redirect('Sign:pwdnew', ["pwd" => $data]);
        }
    }
    
    public function renderIn() {
        $this->template->multiple = $this->supplier->getTapi_config()["multiple_team"];
        $is = $this->is->getData();
        $this->template->teamName = strtoupper($is->teamName);
        $this->translator->setLocale(self::LOCALES[$is->defaultLangugeCode]);

        if ($tk = $this->getRequest()->getParameter("tk")) {
            try {
                $this->tapiAuthenticator->setTapiService($this->tapiService);
                $this->tkLogin($tk);
            } catch (APIException $exc) {
                switch ($exc->getMessage()) {
                    case "Login not approved":
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotApproved"), "danger");
                        break;
                    default:
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotSuccesfull") . ' (' . $exc->getMessage() . ")", "danger");
                        break;
                }
            }
            if (!is_null($this->user->getIdentity()))
                Debugger::log($this->user->getIdentity()->data["callName"] . "@" . $this->supplier->getTym() . " logged in using transfer key");
            $this->redirect('Homepage:');
        }
    }

    private function tkLogin($tk){
        $this->user->logout(true);
        $this->user->login($this->tapiAuthenticator->tkAuthenticate($tk));
    }
    
    private function resetPwd($code){
        return $this->pwdReset->init()
                    ->setCode($code)
                    ->getData();
    }

}
