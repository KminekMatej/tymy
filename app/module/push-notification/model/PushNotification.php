<?php
namespace Tymy\Module\PushNotification\Model;

use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of PushNotification
 *
 * @author kminekmatej, 14. 11. 2021, 21:29:25
 */
class PushNotification implements \JsonSerializable
{

    private int $userId;
    private int $teamId;
    private string $title;
    private string $message;
    private ?string $imageUrl = null;
    private ?int $badge = null;

    public function __construct(int $userId, int $teamId, string $title, string $message, ?string $imageUrl, ?int $badge)
    {
        $this->userId = $title;
        $this->teamId = $teamId;
        $this->title = $title;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->badge = $badge;
    }

    public function jsonSerialize()
    {
        $array = [
            "userId" => $this->userId,
            "teamId" => $this->teamId,
            "title" => $this->title,
            "message" => $this->message,
            "image" => $this->imageUrl,
            "badge" => $this->badge,
        ];

        if (isset($this->imageUrl)) {
            $array["image"] = $this->imageUrl;
        }

        if (isset($this->badge)) {
            $array["badge"] = $this->badge;
        }

        return $array;
    }
}
