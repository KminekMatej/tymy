<?php
namespace Tymy\Module\Sign\Form;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class PwdLostFormFactory
{

    use SmartObject;

    /**
     * @return Form
     */
    public function create(): Form
    {

        $form = new Form();

        $form->addText('email')
            ->setAttribute("placeholder", "E-mail")
            ->addRule(Form::EMAIL, "Nesprávný formát e-mailu")
            ->setRequired('Vyplňte e-mailovou adresu na kterou je Váš účet registrován');

        $form->addSubmit('send', 'ZASLAT KÓD');
        return $form;
    }
}
