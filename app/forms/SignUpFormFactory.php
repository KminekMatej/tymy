<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model;
use App\Model\TapiAuthenticator;


class SignUpFormFactory
{
	use Nette\SmartObject;

	const PASSWORD_MIN_LENGTH = 3;
        const PASSWORD_PATTERN = '[^\s]{3,}';
        const EMAIL_PATTERN = "^[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~]+(\\.[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~]+)*@[a-zA-Z0-9-]+(\\.[a-zA-Z0-9-]+)+";
        const LOGIN_PATTERN = '^[\w-]{3,20}';
        
        

	/** @var FormFactory */
	private $factory;

	/** @var TapiAuthenticator */
	private $tapiAuthenticator;


	public function __construct(FormFactory $factory, TapiAuthenticator $tapiAuthenticator)
	{
		$this->factory = $factory;
		$this->tapiAuthenticator = $tapiAuthenticator;
	}


	/**
	 * @return Form
	 */
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Uživatelské jméno je povinné')
                        ->addRule($form::PATTERN, "Uživatelské jméno musí mít 3-20 znaků", self::LOGIN_PATTERN);

                $form->addPassword('password', 'Heslo:')
			->setRequired('Heslo je povinné')
                        ->addRule($form::PATTERN, "Heslo musí mít minimálně 3 znaky", self::PASSWORD_PATTERN);
		
                $form->addPassword('password_check', 'Heslo znovu:')
			->setRequired('Vyplňte heslo pro kontrolu znovu')
                        ->addConditionOn($form['password'], Form::VALID)
                        ->addRule($form::EQUAL, "Hesla se neshodují", $form['password']);
                
                $form->addEmail('email', 'E-mail:')
			->setRequired('E-mail je povinný')
                        ->addRule($form::PATTERN, "E-mail je invalidní", self::EMAIL_PATTERN);

                $form->addText('firstName', 'Křestní jméno:');
                $form->addText('lastName', 'Příjmení:');

		$form->addTextArea('admin_note', 'Vzkaz pro admina:');

		$form->addSubmit('send', 'Registrovat');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->tapiAuthenticator->add($values->username, $values->password, $values->email, $values->firstName, $values->lastName, $values->admin_note);
			} catch (\Nette\InvalidArgumentException $exc) {
				$form['username']->addError($exc->getMessage());
				return;
			}
			$onSuccess();
		};

		return $form;
	}

}
