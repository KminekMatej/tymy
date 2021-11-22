<?php
namespace Tymy\Module\PushNotification\Manager;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;
use Tymy\Module\Team\Manager\TeamManager;

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

    public function sendOneNotification(Subscriber $subscriber, PushNotification $pushNotification)
    {
        if ($subscriber->getType() !== Subscriber::TYPE_APNS) {
            return;
        }

        $team = $this->teamManager->getTeam();
        $now = new DateTimeImmutable();
        $apns_topic = 'tymy.cz.ios-application';

        //generate JWT token
        $token = $this->jwtConfiguration->builder()
            ->issuedBy($team->getSysName() . ".tymy.cz")
            ->issuedAt($now)
            ->identifiedBy('4f1g23a12aa')
            ->expiresAt($now->modify('+2 hours'))
            ->withClaim("teamId", $pushNotification->getTeamId())
            ->withClaim("userId", $pushNotification->getUserId())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

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

        $url = self::URL_DEV . "/{$subscriber->getSubscription()}";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJSON);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "apns-topic: $apns_topic"]);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
}
