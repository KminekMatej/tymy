<?php

namespace Tymy\Module\Core\Service;

use Kdyby\Translation\Translator;
use Latte\Engine;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tymy\Bootstrap;
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

    private TeamManager $teamManager;
    private Translator $translator;
    private Team $team;
    private string $teamDomain;
    private LinkGenerator $linkGenerator;
    private ITemplateFactory $templateFactory;
    private Mailer $mailSender;
    private StringsManager $stringsManager;

    public function __construct(TeamManager $teamManager, LinkGenerator $linkGenerator, ITemplateFactory $templateFactory, Mailer $mailer, StringsManager $stringsManager, Translator $translator, User $user)
    {
        $this->teamManager = $teamManager;
        $this->linkGenerator = $linkGenerator;
        $this->templateFactory = $templateFactory;
        $this->mailSender = $mailer;
        $this->stringsManager = $stringsManager;
        $this->translator = $translator;
        $this->user = $user;
    }

    private function startup()
    {
        if (empty($this->team)) {
            $this->team = $this->teamManager->getTeam();
            $this->teamDomain = $this->team->getSysName() . ".tymy.cz";
        }
    }

    private function createTemplate($templateName, $params)
    {
        $template = $this->templateFactory->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        return $template->renderToString(self::TEMPLATES_PATH . "$templateName.latte", $params);
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
     * @param string $name
     * @param string $email
     * @return void
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
     * @param string $name
     * @param string $email
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

    /**
     * Compose & send email with invitation of user into this team
     * @param string $nameTo
     * @param string $emailTo
     * @param string $nameFrom
     * @param string $invitationUrl
     * @param DateTime $invitationValidity
     * @return void
     */
    public function mailInvitation(string $nameTo, string $emailTo, string $nameFrom, string $invitationUrl, DateTime $invitationValidity): void
    {
        $this->startup();

        $latte = new Engine();

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
