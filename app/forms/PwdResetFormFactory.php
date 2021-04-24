<?php

namespace Tymy\App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tymy\App\Model\Supplier;

class PwdResetFormFactory {

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
    public function create() {

        $form = $this->factory->create();

        $form->addText('code')
                ->setAttribute("placeholder", "Kód")
                ->addRule(Form::LENGTH, "Kód není zadán správně", 20)
                ->setRequired('Vyplňte Váš RESET kód, který Vám přišel mailem');
        
        $form->addSubmit('send', 'RESET');
        
        return $form;
    }

}
