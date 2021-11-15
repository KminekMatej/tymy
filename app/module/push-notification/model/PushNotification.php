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

    private string $message;
    private string $title;
    private ?string $imageUrl = null;
    private ?int $badge = null;

    public function __construct(string $message, string $title, ?string $imageUrl, ?int $badge)
    {
        $this->message = $message;
        $this->title = $title;
        $this->imageUrl = $imageUrl;
        $this->badge = $badge;
    }

    public function jsonSerialize(): mixed
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
