<?php

namespace App\Presenters;

use Nette;
use App\Forms;

class SignPresenter extends BasePresenter {

    /** @var Forms\SignInFormFactory @inject */
    public $signInFactory;

    /** @var Forms\SignUpFormFactory @inject */
    public $signUpFactory;
    
    /** @var \App\Model\Supplier @inject */
    public $supplier;

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
                    $this->redirect('Homepage:');
                });
    }

    public function actionOut() {
        if (isset($this->getUser()->getIdentity())) {
            $this->supplier->setTapi_config($this->getUser()->getIdentity()->getData()["tapi_config"]);
            $logout = new \Tymy\Logout(NULL, $this);
            $logout
                    ->setSupplier($this->supplier)
                    ->logout();

            $this->getUser()->logout();
        }
        $this->flashMessage('You have been succesfully signed out');
        $this->redirect('Sign:In');
    }

}
