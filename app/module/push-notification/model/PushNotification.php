<?php

namespace Tymy\Module\PushNotification\Model;

use JsonSerializable;

/**
 * Description of PushNotification
 *
 * @author kminekmatej, 14. 11. 2021, 21:29:25
 */
class PushNotification implements JsonSerializable
{
    private array $params;

    public function __construct(private string $type, private int $userId, private int $teamId, private string $title, private string $message, private ?string $imageUrl, private ?int $badge, array $params = [])
    {
        $this->params = $params + ["type" => $type];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTeamId(): int
    {
        return $this->teamId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getBadge(): ?int
    {
        return $this->badge;
    }

    /**
     * @return mixed[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function addParam(string $name, $value): static
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $array = [
            "userId" => $this->userId,
            "teamId" => $this->teamId,
            "title" => $this->title,
            "message" => $this->message,
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
