<?php
namespace Tymy\Module\Sign\Form;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class PwdResetFormFactory
{

    use SmartObject;

    /**
     * @return Form
     */
    public function create(): Form
    {

        $form = new Form();

        $form->addText('code')
            ->setAttribute("placeholder", "Kód")
            ->addRule(Form::LENGTH, "Kód není zadán správně", 20)
            ->setRequired('Vyplňte Váš RESET kód, který Vám přišel mailem');

        $form->addSubmit('send', 'RESET');

        return $form;
    }
}
