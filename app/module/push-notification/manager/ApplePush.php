<?php

namespace Tymy\Module\PushNotification\Manager;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\MultibyteStringConverter;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Tracy\Debugger;
use Tracy\ILogger;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of ApplePush
 */
class ApplePush
{
    private const URL_SANDBOX = "https://api.sandbox.push.apple.com/3/device";
    private const URL_PRODUCTION = "https://api.push.apple.com/3/device";
    private array $expiredSubscribers = [];

    public function __construct(private array $apns, private Configuration $jwtConfiguration, private TeamManager $teamManager)
    {
    }

    public function sendBulkNotifications(array $subscribers, PushNotification $pushNotification): void
    {
        $this->expiredSubscribers = [];

        if (empty($subscribers)) {
            return;
        }

        $team = $this->teamManager->getTeam();
        $now = new DateTimeImmutable();

        //generate JWT token
        $token = $this->jwtConfiguration->builder()
            ->issuedBy($this->apns['teamId'])
            ->issuedAt($now)
            ->withHeader('kid', $this->apns['keyId'])
            ->getToken(new Sha256(new MultibyteStringConverter()), InMemory::file(ROOT_DIR . "/" . $this->apns['key']));

        $headers = [
            "apns-topic: " . $this->apns['topic'],
            "apns-push-type: alert",
            "Authorization: Bearer {$token->toString()}",
        ];

        $pushNotification->addParam("team", $team->getExtendedSysName());

        $payloadJSON = json_encode([
            'aps' => [
                'alert' => [
                    'title' => $pushNotification->getTitle(), //frankie posted in Důležité
                    'body' => $pushNotification->getMessage(), //obsah postu
                ],
                'data' => $pushNotification->getParams(),
                'sound' => 'default',
                'badge' => $pushNotification->getBadge() ?: 1, //zatim vzdycky 1
                'content-available' => 1,
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJSON);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        foreach ($subscribers as $subscriber) {
            assert($subscriber instanceof Subscriber);
            if ($subscriber->getType() !== Subscriber::TYPE_APNS) {
                continue;
            }

            $url = ($this->apns['mode'] == "sandbox" ? self::URL_SANDBOX : self::URL_PRODUCTION) . "/{$subscriber->getSubscription()}";
            curl_setopt($ch, CURLOPT_URL, $url);

            $response = curl_exec($ch);
            $info = curl_getinfo($ch);

            if ($response === false || $info["http_code"] !== 200) {
                $decodedResponse = json_decode($response, null, 512, JSON_THROW_ON_ERROR);
                $errorReason = $decodedResponse->reason ?? null;
                if ($errorReason === "BadDeviceToken") {
                    //invalid device id, delete from database
                    $this->expiredSubscribers[] = $subscriber;
                }
                Debugger::log("APNS notifikace nemohla být odeslána, chyba: " . $response . ", infodata: " . json_encode($info, JSON_THROW_ON_ERROR), ILogger::WARNING);
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getExpiredSubscribers(): array
    {
        return $this->expiredSubscribers;
    }
}
