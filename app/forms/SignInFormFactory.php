<?php
namespace Tymy\App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class SignInFormFactory
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
    public function create(callable $onSuccess): Form
    {

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
