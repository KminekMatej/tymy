<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette;
use Nette\Application\UI\Form;
use Nette\NotImplementedException;
use Tracy\Debugger;
use Tymy\Module\Authentication\Manager\AuthenticationManager;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Presenter\Front\BasePresenter;
use Tymy\Module\Sign\Form\SignInFormFactory;

class InPresenter extends BasePresenter
{
    /** @inject */
    public SignInFormFactory $signInFactory;

    /** @inject */
    public AuthenticationManager $authenticationManager;

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        return $this->signInFactory->create(function (Form $form, $values) {
            try {
                $this->user->setExpiration('20 minutes');
                $this->user->login($values->name, $values->password);
                BaseManager::logg($this->team, "{$values->name} application login");
            } catch (Nette\Security\AuthenticationException $exc) {
                switch ($exc->getMessage()) {
                    case "Login not approved":
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotApproved"), "danger");
                        break;
                    default:
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotSuccesfull") . ' (' . $exc->getMessage() . ")", "danger");
                        break;
                }
            }
            if ($this->user->isLoggedIn()) {
                $this->initUser();
                Debugger::log($this->tymyUser->getCallName() . "@" . $this->team->getSysName() . " logged in");
            }
            $this->redirect(':Core:Default:');
        });
    }

    public function renderDefault()
    {
        if ($tk = $this->getRequest()->getParameter("tk")) {
            try {
                $this->tkLogin($tk);
            } catch (Nette\Security\AuthenticationException $exc) {
                switch ($exc->getMessage()) {
                    case "Login not approved":
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotApproved"), "danger");
                        break;
                    default:
                        $this->flashMessage($this->translator->translate("common.alerts.loginNotSuccesfull") . ' (' . $exc->getMessage() . ")", "danger");
                        break;
                }
            }
            if ($this->user->isLoggedIn()) {
                $this->initUser();
                Debugger::log($this->tymyUser->getCallName() . "@" . $this->team->getSysName() . " logged in using transfer key");
            }
            $this->redirect(':Core:Default:');
        }
    }

    /**
     * Validate transfer key and log user if its valid
     */
    private function tkLogin(string $tk): void
    {
        $this->user->logout(true);

        $this->user->login("tk|$tk");
    }
}
