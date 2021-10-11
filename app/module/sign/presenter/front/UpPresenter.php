<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette;
use Tymy\App\Forms\SignUpFormFactory;
use Tymy\Module\Core\Presenter\Front\BasePresenter;

class UpPresenter extends BasePresenter
{

    /** @inject */
    public SignUpFormFactory $signUpFactory;

    /**
     * Sign-up form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignUpForm()
    {
        return $this->signUpFactory->create(function () {
                    $this->flashMessage($this->translator->translate("common.alerts.registrationSuccesfull"), 'success');
                    $this->redirect(':Sign:In:');
                });
    }

}