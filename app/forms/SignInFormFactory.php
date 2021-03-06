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
    
    /** @var \App\Model\Supplier */
    private $supplier;
    
    private $tapi_config;
    
    
    
    
    public function __construct(FormFactory $factory, User $user, \App\Model\Supplier $supplier) {
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
        
        if ($this->tapi_config["multiple_team"]) {
            $teamlist = [
                "dev" => "dev.tymy.cz",
                "fuj" => "fuj.tymy.cz",
                "atruc" => "atruc.tymy.cz",
                "p7" => "p7.tymy.cz",
                "dubaj" => "dubaj.tymy.cz",
                "ks" => "ks.tymy.cz",
                "preview" => "preview.tymy.cz",
                "gaudeamus" => "gaudeamus.tymy.cz",
                "brno" => "brno.tymy.cz",
                "monkeys" => "monkeys.tymy.cz",
                "pd" => "pd.tymy.cz",
                "vocem" => "vocem.tymy.cz"];

            $form->addSelect('team', '', $teamlist)
                    ->setPrompt('Vyberte tým ↓')
                    ->setRequired('Vyberte tým');
        }

        $form->addSubmit('send', 'LOGIN');
        $form->onSuccess[] = [$this, 'formValid'];
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        
        return $form;
    }
    
    public function formValid(Form $form, $values){
        if ($this->tapi_config["multiple_team"]) {
            $this->tapi_config["tym"] = $values["team"];
            $this->supplier->setTapi_config($this->tapi_config);
        }
    }

}
