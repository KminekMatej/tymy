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

class SignPresenter extends BasePresenter
{
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
    public $logout;
    public $pwdLost;
    public $pwdReset;

    /** @inject */
    public AuthenticationManager $authenticationManager;

}