<?php

namespace Tymy\Module\Sign\Form;

use Nette;
use Nette\Application\UI\Form;
use Tymy\Module\Core\Exception\MissingInputException;
use Tymy\Module\User\Manager\InvitationManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\Invitation;

class SignUpFormFactory
{
    use Nette\SmartObject;

    public const PASSWORD_MIN_LENGTH = 3;
    public const PASSWORD_PATTERN = '[^\s]{3,}';
    public const EMAIL_PATTERN = "^[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~]+(\\.[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~]+)*@[a-zA-Z0-9-]+(\\.[a-zA-Z0-9-]+)+";
    public const LOGIN_PATTERN = '^[\w-]{3,20}';

    private UserManager $userManager;
    private InvitationManager $invitationManager;

    public function __construct(UserManager $userManager, InvitationManager $invitationManager)
    {
        $this->userManager = $userManager;
        $this->invitationManager = $invitationManager;
    }

    /**
     * @return Form
     */
    public function create(callable $onSuccess, ?Invitation $invitation = null)
    {
        $form = new Form();

        $form->addHidden("invitation", $invitation ? $invitation->getCode() : null);

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

        // fill details from invitation
        if ($invitation) {
            $form['firstName']->setValue($invitation->getFirstName());
            $form['lastName']->setValue($invitation->getLastName());
            $form['email']->setValue($invitation->getEmail());
        }

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $invitation = null;
                if ($values->invitation && !empty($values->invitation)) {
                    $invitation = $this->invitationManager->getByCode($values->invitation);
                    if (!$invitation) {
                        if ($invitation->getStatus() == Invitation::STATUS_EXPIRED) { //already expired
                            $form->addError($this->translator->translate("team.errors.invitationExpired", 1));
                            return;
                        } elseif ($invitation->getStatus() == Invitation::STATUS_ACCEPTED) {
                            $form->addError($this->translator->translate("team.errors.invitationAccepted", 1));
                            return;
                        }
                    }
                }

                $this->userManager->register([
                    "login" => $values->username,
                    "password" => $values->password,
                    "email" => $values->email,
                    "firstName" => $values->firstName,
                    "lastName" => $values->lastName,
                    "note" => $values->admin_note,
                    "invitation" => $values->invitation,
                ], $invitation);
            } catch (\Nette\InvalidArgumentException $exc) {
                $form['username']->addError($exc->getMessage());
                return;
            } catch (MissingInputException $exc) {
                $form[$exc->getMessage()]->addError("This field is required");
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}
