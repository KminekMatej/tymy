<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette;
use Nette\Security\SimpleIdentity;
use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\Sign\Form\SignUpFormFactory;

class UpPresenter extends BasePresenter
{
    /** @inject */
    public SignUpFormFactory $signUpFactory;

    /**
     * Sign-up form factory.
     */
    protected function createComponentSignUpForm(): \Nette\Application\UI\Form
    {
        return $this->signUpFactory->create(function (SimpleIdentity $registeredIdentity): void {
                $this->flashMessage($this->translator->translate("common.alerts.registrationSuccesfull") . " " . $this->translator->translate("common.alerts.waitForApproval"), 'success');
                $this->redirect(':Sign:In:');
        });
    }
}
