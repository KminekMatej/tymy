<?php

namespace Tymy\Module\Autotest\Manager;

use BoostSpace\Test\Entity\Assert;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * Description of MockMailer
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 15.03.2023
 */
class MockMailer implements Mailer
{
    public static array $lastMail = [];

    public static function assertEmpty()
    {
        Assert::count(0, self::$lastMail, "There are remains in lastMail: " . \json_encode(self::$lastMail));
    }

    public static function assertSent($to, $subject, $from = null, $body = null)
    {
        if (is_string($to)) { //simply check last sent mail
            $lastMail = array_pop(self::$lastMail);
            self::checkLastMail($lastMail, $to, $subject, $from, $body);
        } elseif (is_array($to)) {//assert sent to multiple recipients
            Assert::count(count($to), self::$lastMail, "Expected e-mails count doesnt match - expected To: <" . join(">, <", $to) . ">, Subject '$subject'");
            foreach ($to as $toOne) {
                $lastMail = array_pop(self::$lastMail);
                self::checkLastMail($lastMail, $toOne, $subject, $from, $body);
            }
            Assert::count(0, self::$lastMail);
        }
    }

    private static function checkLastMail($lastMail, $to, $subject, $from = null, $body = null)
    {
        Assert::hasKey("subject", $lastMail, "Last mail does not contains key subject");
        Assert::hasKey("from", $lastMail, "Last mail does not contains key from");
        Assert::hasKey("to", $lastMail, "Last mail does not contains key to");
        Assert::hasKey("body", $lastMail, "Last mail does not contains key body");

        Assert::type("string", $lastMail["subject"]);
        Assert::type("array", $lastMail["from"]);
        $fromHeader = array_keys($lastMail["from"])[0];
        Assert::type("string", $fromHeader);
        $toHeader = array_keys($lastMail["to"])[0];
        Assert::type("string", $toHeader);
        Assert::type("string", $lastMail["body"]);

        Assert::equal($to, $toHeader, "Last mail To mismatch");
        Assert::equal($subject, $lastMail["subject"], "Last mail Subject mismatch");

        if ($from) {
            Assert::equal($from, $fromHeader, "Last mail From mismatch");
        }
        if ($body) {
            Assert::equal($body, $lastMail["body"], "Last mail Body mismatch");
        }
    }

    public function send(Message $mail): void
    {
        if (str_starts_with($mail->getSubject(), "PHP: An error occurred on the server")) {
            //autotest error, meaning to ignore
        } else {
            self::$lastMail[] = [
                "subject" => $mail->getSubject(),
                "from" => $mail->getFrom(),
                "to" => $mail->getHeader("To"),
                "body" => $mail->getBody(),
            ];
        }
    }
}
