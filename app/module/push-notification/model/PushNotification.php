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

    private string $title;
    private string $message;
    private ?string $imageUrl = null;
    private ?int $badge = null;

    public function __construct(string $title, string $message, ?string $imageUrl, ?int $badge)
    {
        $this->title = $title;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->badge = $badge;
    }

    public function jsonSerialize()
    {
        $array = [
            "title" => $this->title,
            "message" => $this->message,
            "image" => $this->imageUrl,
            "image" => $this->imageUrl,
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
