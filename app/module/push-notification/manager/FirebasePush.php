<?php
namespace Tymy\Module\PushNotification\Manager;

use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of AndroidPush
 *
 * @author kminekmatej, 22. 11. 2021, 12:28:27
 */
class FirebasePush
{

    private const URL = "https://fcm.googleapis.com/fcm/send";

    private array $fcm;
    private TeamManager $teamManager;

    public function __construct(array $fcm, TeamManager $teamManager)
    {
        $this->fcm = $fcm;
        $this->teamManager = $teamManager;
    }

    /**
     * Send multiple notifications using FCM
     * 
     * @param string[] $deviceIds
     * @param PushNotification $pushNotification
     * @return void
     */
    public function sendBulkNotifications(array $deviceIds, PushNotification $pushNotification): void
    {
        if (empty($deviceIds)) {
            return;
        }

        $fcmApiKey = $this->fcm["key"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $fcmApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $payload = json_encode([
            'registration_ids' => $deviceIds,
            'data' => [
                'message' => $pushNotification->getMessage(),
                'title' => $pushNotification->getTitle()
            ]
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $info["http_code"] !== 200) {
            //todo: handle error
        }

        curl_close($ch);
    }
}
