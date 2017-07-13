<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model;


class SignUpFormFactory
{
	use Nette\SmartObject;

	const PASSWORD_MIN_LENGTH = 3;
        const PASSWORD_PATTERN = '[^\s]{3,}';
        const EMAIL_PATTERN = "^[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~]+(\\.[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~]+)*@[a-zA-Z0-9-]+(\\.[a-zA-Z0-9-]+)+";
        const LOGIN_PATTERN = '^[\w-]{3,20}';
        
        

	/** @var FormFactory */
	private $factory;

	/** @var Model\UserManager */
	private $tapiAuthenticator;


	public function __construct(FormFactory $factory, Model\TapiAuthenticator $tapiAuthenticator)
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
		$form->addText('username', 'Pick a username:')
			->setRequired('Please pick a username.')
                        ->addRule($form::PATTERN, "Uživatelské jméno musí mít 3-20 znaků", self::LOGIN_PATTERN);

		$form->addEmail('email', 'Your e-mail:')
			->setRequired('Please enter your e-mail.');

		$form->addPassword('password', 'Create a password:')
			->setRequired('Please create a password.')
                        ->addRule($form::PATTERN, "Heslo musí mít minimálně 3 znaky", self::PASSWORD_PATTERN);

		$form->addSubmit('send', 'Registrovat');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->tapiAuthenticator->add($values->username, $values->email, $values->password);
			} catch (\Nette\InvalidArgumentException $exc) {
				$form['username']->addError($exc->getMessage());
				return;
			}
			$onSuccess();
		};

		return $form;
	}

}
