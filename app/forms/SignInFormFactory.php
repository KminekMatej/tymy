<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;

class SignInFormFactory {

    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var User */
    private $user;

    public function __construct(FormFactory $factory, User $user) {
        $this->factory = $factory;
        $this->user = $user;
    }

    /**
     * @return Form
     */
    public function create(callable $onSuccess) {

        $form = $this->factory->create();

        $form->addText('name')
                ->setRequired()
                ->setAttribute("placeholder", "uživatelské jméno")
                ->setRequired('Vyplňte své uživatelské jméno');

        $form->addPassword('password')
                ->setRequired()
                ->setAttribute("placeholder", "heslo")
                ->setRequired('Vyplňte své heslo');
        
        $form->addSubmit('send', 'LOGIN');
        
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $this->user->setExpiration('20 minutes');
                $this->user->login($values->name, $values->password);
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError('The username or password you entered is incorrect.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }

}
