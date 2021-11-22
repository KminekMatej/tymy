<?php
namespace Tymy\Module\PushNotification\Manager;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;
use Tymy\Module\Team\Manager\TeamManager;
use function GuzzleHttp\json_encode;

/**
 * Description of ApplePush
 *
 * @author kminekmatej, 22. 11. 2021, 12:28:27
 */
class ApplePush
{

    private const URL_DEV = "https://api.sandbox.push.apple.com/3/device";

    private Configuration $jwtConfiguration;
    private TeamManager $teamManager;

    public function __construct(Configuration $jwtConfiguration, TeamManager $teamManager)
    {
        $this->jwtConfiguration = $jwtConfiguration;
        $this->teamManager = $teamManager;
    }

    public function sendBulkNotifications(array $subscribers, PushNotification $pushNotification)
    {
        if (empty($subscribers)) {
            return;
        }

        $team = $this->teamManager->getTeam();
        $now = new DateTimeImmutable();
        $apns_topic = 'tymy.cz.ios-application';

        $payloadJSON = json_encode([
            'aps' => [
                'alert' => [
                    'title' => $pushNotification->getTitle(),
                    'body' => $pushNotification->getMessage(),
                ],
                'sound' => 'default',
                'badge' => $pushNotification->getBadge() ?: 1
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "apns-topic: $apns_topic"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJSON);

        //generate JWT token
        $token = $this->jwtConfiguration->builder()
            ->issuedBy($team->getSysName() . ".tymy.cz")
            ->issuedAt($now)
            ->identifiedBy('4f1g23a12aa')
            ->expiresAt($now->modify('+2 hours'))
            ->withClaim("teamId", $pushNotification->getTeamId())
            ->withClaim("userId", $pushNotification->getUserId())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

        foreach ($subscribers as $subscriber) {
            /* @var $subscriber Subscriber */
            if ($subscriber->getType() !== Subscriber::TYPE_APNS) {
                continue;
            }

            curl_setopt($ch, CURLOPT_URL, self::URL_DEV . "/{$subscriber->getSubscription()}");

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false || $httpcode !== 200) {
                //todo: handle error
            }
        }
    }
}
