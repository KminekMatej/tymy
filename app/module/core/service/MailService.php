<?php

namespace Tymy\Module\Core\Service;

use Contributte\Translation\Translator;
use Latte\Engine;
use Latte\Essential\TranslatorExtension;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tymy\Bootstrap;
use Tymy\Module\Core\Manager\StringsManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;

/**
 * Description of MailService
 */
class MailService
{
    public const TEMPLATES_PATH = __DIR__ . "/../templates/mail";
    public const ROBOT_EMAIL_FROM_S = "robot@tymy.cz";
    private Team $team;
    private string $teamDomain;

    public function __construct(private TeamManager $teamManager, private Mailer $mailer, private StringsManager $stringsManager, private Translator $translator)
    {
    }

    private function startup(): void
    {
        if (empty($this->team)) {
            $this->team = $this->teamManager->getTeam();
            $this->teamDomain = $this->team->getSysName() . ".tymy.cz";
        }
    }

    public function mailUserRegistered(string $nameTo, string $emailTo, string $login, string $email, ?string $firstName = null, ?string $lastName = null, ?string $note = ""): void
    {
        $this->startup();
        $body = $this->stringsManager->translateBy("register", "reg_mail_body_5s", $login, $firstName, $lastName, $email, $note);
        $subject = $this->teamDomain . ": " . $this->stringsManager->translateBy("register", "reg_mail_subj", $this->teamDomain);
        $this->sendMail($nameTo, $emailTo, $body, $subject, $email);
    }

    /**
     * Send email to user that his registration has been approved
     */
    public function mailLoginApproved(string $name, string $email): void
    {
        $this->startup();
        $body = $this->translator->translate("team.registrationApproved", $this->teamDomain);
        $subject = $this->translator->translate("team.registrationApprovedSubject", $this->teamDomain);
        $this->sendMail($name, $email, $body, $subject);
    }

    /**
     * Send email to user that his registration has been denied
     */
    public function mailLoginDenied(string $name, string $email): void
    {
        $this->startup();
        $body = $this->translator->translate("team.registrationDenied", $this->teamDomain);
        $subject = $this->translator->translate("team.registrationDeniedSubject", $this->teamDomain);
        $this->sendMail($name, $email, $body, $subject);
    }

    public function mailPwdReset(string $name, string $email, string $callbackUri, string $hostName, string $resetCode): void
    {
        $this->startup();
        $body = $this->stringsManager->translateBy("pswd_reset", "rc_mail_body_4s", $hostName, $this->teamDomain, $resetCode, sprintf($callbackUri, $resetCode));
        $subject = "{$this->teamDomain}: " . $this->stringsManager->translateBy("pswd_reset", "pswd_mail_subj");
        $this->sendMail($name, $email, $body, $subject);
    }

    /**
     * Compose & send email with invitation of user into this team
     */
    public function mailInvitation(string $nameTo, string $emailTo, string $nameFrom, string $invitationUrl, DateTime $invitationValidity): void
    {
        $this->startup();

        $latte = new Engine();

        $translatorExtension = new TranslatorExtension(
            $this->translator->translate(...),
        );
        $latte->addExtension($translatorExtension);

        $subject = $this->translator->translate("mail.invitation.subject");

        $body = $latte->renderToString(Bootstrap::MODULES_DIR . "/core/mail/invitation.latte", [
            "teamPortalUrl" => $this->teamDomain,
            "invitationUrl" => $invitationUrl,
            "validity" => $invitationValidity,
            "teamName" => $this->team->getName(),
            "invitationCreator" => $nameFrom,
        ]);

        $this->sendMail($nameTo, $emailTo, $body, $subject);
    }

    private function sendMail(string $name, string $email, string $body, ?string $subject = null, ?string $replyTo = null): void
    {
        try {
            $mail = new Message();
            $mail->setFrom(self::ROBOT_EMAIL_FROM_S, "Robot " . ucfirst($this->team->getSysName()) . ".tymy.cz")
                ->addTo(trim($email), $name)
                ->setSubject($subject)
                ->setReturnPath(self::ROBOT_EMAIL_FROM_S)
                ->setBody($body);

            if ($replyTo) {
                $mail->addReplyTo(trim($replyTo));
            }

            $this->mailer->send($mail);
        } catch (SendException $exc) {
            Debugger::log("Failed to send email from team {$this->team->getSysName()} to $email. Error: {$exc->getMessage()}");
        }
    }
}
