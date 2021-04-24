<?php

namespace Tymy\App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tymy\App\Model\Supplier;

class SignInFormFactory {

    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var User */
    private $user;
    
    /** @var Supplier */
    private $supplier;
    
    private $tapi_config;
    
    
    
    
    public function __construct(FormFactory $factory, User $user, Supplier $supplier) {
        $this->factory = $factory;
        $this->user = $user;
        $this->supplier = $supplier;
        $this->tapi_config = $supplier->getTapi_config();
    }

    /**
     * @return Form
     */
    public function create(callable $onSuccess) {

        $form = $this->factory->create();

        $form->addText('name')
                ->setAttribute("placeholder", "uživatelské jméno")
                ->setRequired('Vyplňte své uživatelské jméno');

        $form->addPassword('password')
                ->setAttribute("placeholder", "heslo")
                ->setRequired('Vyplňte své heslo');
        
        $form->addSubmit('send', 'LOGIN');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };

        return $form;
    }
}