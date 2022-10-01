<?php

namespace Tymy\Module\Core\Service;

use Kdyby\Translation\Translator;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tymy\Module\Core\Manager\StringsManager;
use Tymy\Module\Team\Manager\TeamManager;
use Tymy\Module\Team\Model\Team;

/**
 * Description of MailService
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 6. 9. 2020
 */
class MailService
{
    public const TEMPLATES_PATH = __DIR__ . "/../templates/mail";
    public const ROBOT_EMAIL_FROM_S = "robot@%s.tymy.cz";
    private Team $team;
    private string $teamDomain;

    public function __construct(private TeamManager $teamManager, LinkGenerator $linkGenerator, ITemplateFactory $templateFactory, private Mailer $mailSender, private StringsManager $stringsManager, private Translator $translator)
    {
    }

    private function startup()
    {
        if (empty($this->team)) {
            $this->team = $this->teamManager->getTeam();
            $this->teamDomain = $this->team->getSysName() . ".tymy.cz";
        }
    }

    public function mailUserRegistered(string $nameTo, string $emailTo, string $login, string $email, ?string $firstName = null, ?string $lastName = null, ?string $note = "")
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

    public function mailPwdReset(string $name, string $email, string $callbackUri, string $hostName, string $resetCode)
    {
        $this->startup();
        $body = $this->stringsManager->translateBy("pswd_reset", "rc_mail_body_4s", $hostName, $this->teamDomain, $resetCode, sprintf($callbackUri, $resetCode));
        $subject = "{$this->teamDomain}: " . $this->stringsManager->translateBy("pswd_reset", "pswd_mail_subj");
        $this->sendMail($name, $email, $body, $subject);
    }

    private function sendMail(string $name, string $email, string $body, ?string $subject = null, ?string $replyTo = null): void
    {
        $mail = new Message();
        $mail->setFrom(sprintf(self::ROBOT_EMAIL_FROM_S, $this->team->getSysName()), $this->team->getSysName())
            ->addTo(trim($email), $name)
            ->setSubject($subject)
            ->setBody($body);

        if ($replyTo) {
            $mail->addReplyTo(trim($replyTo));
        }

        $this->mailSender->send($mail);
    }
}
