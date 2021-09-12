<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Nette;
use Nette\Application\UI\Form;
use Nette\NotImplementedException;
use stdClass;
use Tapi\Exception\APIException;
use Tracy\Debugger;
use Tymy\App\Forms\PwdLostFormFactory;
use Tymy\App\Forms\PwdResetFormFactory;
use Tymy\App\Forms\SignInFormFactory;
use Tymy\App\Forms\SignUpFormFactory;
use Tymy\App\Model\Supplier;
use Tymy\Module\Authentication\Manager\AuthenticationManager;
use Tymy\Module\Core\Presenter\Front\BasePresenter;

class InPresenter extends BasePresenter
{

    /** @inject */
    public SignInFormFactory $signInFactory;

    /** @inject */
    public Supplier $supplier;

    /** @inject */
    public AuthenticationManager $authenticationManager;

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = $this->signInFactory->create(function (Form $form, $values) {
            try {
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
            if (!is_null($this->user->getIdentity()))
                Debugger::log($this->user->getIdentity()->data["callName"] . "@" . $this->supplier->getTym() . " logged in");
            $this->redirect('Homepage:');
        });

        return $form;
    }

    public function renderDefault()
    {
        if ($tk = $this->getRequest()->getParameter("tk")) {
            try {
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

    /** @todo */
    private function tkLogin($tk)
    {
        $this->user->logout(true);
        throw new NotImplementedException();
        $this->user->login($this->authenticationManager->tkAuthenticate($tk));
    }

}