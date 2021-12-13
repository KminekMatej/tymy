<?php
namespace Tymy\App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class PwdResetFormFactory
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

        $form->addText('code')
            ->setAttribute("placeholder", "Kód")
            ->addRule(Form::LENGTH, "Kód není zadán správně", 20)
            ->setRequired('Vyplňte Váš RESET kód, který Vám přišel mailem');

        $form->addSubmit('send', 'RESET');

        return $form;
    }
}
