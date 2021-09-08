<?php

namespace Tymy\Module\Core\Service;

use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tymy\Module\Core\Manager\TranslationManager;
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
    private Team $team;
    private string $teamDomain;
    private LinkGenerator $linkGenerator;
    private ITemplateFactory $templateFactory;
    private Mailer $mailSender;
    private TranslationManager $translationManager;

    public function __construct(TeamManager $teamManager, LinkGenerator $linkGenerator, ITemplateFactory $templateFactory, Mailer $mailer, TranslationManager $translationManager)
    {
        $this->teamManager = $teamManager;
        $this->linkGenerator = $linkGenerator;
        $this->templateFactory = $templateFactory;
        $this->mailSender = $mailer;
        $this->translationManager = $translationManager;
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
        $body = $this->translationManager->translateBy("register", "reg_mail_body_5s", $login, $firstName, $lastName, $email, $note);
        $subject = $this->teamDomain . ": " . $this->translationManager->translateBy("register", "reg_mail_subj", $this->teamDomain);
        $this->sendMail($nameTo, $emailTo, $body, $subject);
    }

    public function mailLoginApproved(string $name, string $email)
    {
        $this->startup();
        $body = $this->translationManager->translateBy("register", "allow_mail_body_1s", $this->teamDomain);
        $subject = $this->translationManager->translateBy("profile", "allow_mail_subj", $this->teamDomain);
        $this->sendMail($name, $email, $body, $subject);
    }

    public function mailLoginDenied(string $name, string $email)
    {
        $this->startup();
        $body = $this->translationManager->translateBy("profile", "deny_mail_body_1s", $this->teamDomain);
        $subject = $this->translationManager->translateBy("profile", "deny_mail_subj", $this->teamDomain);
        $this->sendMail($name, $email, $body, $subject);
    }

    public function mailPwdReset(string $name, string $email, string $callbackUri, string $hostName, string $resetCode)
    {
        $this->startup();
        $body = $this->translationManager->translateBy("pswd_reset", "rc_mail_body_4s", $hostName, $this->teamDomain, $resetCode, sprintf($callbackUri, $resetCode));
        $subject = "{$this->teamDomain}: " . $this->translationManager->translateBy("pswd_reset", "pswd_mail_subj");
        $this->sendMail($name, $email, $body, $subject);
    }

    private function sendMail(string $name, string $email, string $body, string $subject = null)
    {
        \Tracy\Debugger::log("Sending to: $name<$email>, subject $subject: $body");
        \Tracy\Debugger::barDump($body, "Sending to: $name<$email>, subject $subject");
        return; //debug
        $mail = new Message();
        $mail->setFrom(sprintf(self::ROBOT_EMAIL_FROM_S, $this->team->getSysName()), $this->team->getSysName())
                ->addTo($email, $name)
                ->setSubject($subject)
                ->setBody($body);

        $this->mailSender->send($mail);
    }
}
