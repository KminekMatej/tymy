<?php

namespace Tymy\Module\Sign\Form;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class SignInFormFactory
{
    use SmartObject;

    public function create(callable $onSuccess): Form
    {

        $form = new Form();

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
