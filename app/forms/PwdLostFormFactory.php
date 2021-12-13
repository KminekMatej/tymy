<?php
namespace Tymy\App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class PwdLostFormFactory
{

    use SmartObject;

    private FormFactory $factory;

    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return Form
     */
    public function create(): Form
    {

        $form = $this->factory->create();

        $form->addText('email')
            ->setAttribute("placeholder", "E-mail")
            ->addRule(Form::EMAIL, "Nesprávný formát e-mailu")
            ->setRequired('Vyplňte e-mailovou adresu na kterou je Váš účet registrován');

        $form->addSubmit('send', 'ZASLAT KÓD');
        return $form;
    }
}
