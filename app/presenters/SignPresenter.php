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

    /** @var \Tymy\Logout @inject */
    public $logout;
    
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

    public function actionOut() {
        if (!is_null($this->getUser()->getIdentity())) {
            $this->logout->logout();
            $this->getUser()->logout();
        }
        $this->flashMessage('You have been succesfully signed out');
        $this->redirect('Sign:In');
    }

}
