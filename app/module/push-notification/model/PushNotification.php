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
    private string $type;
    private array $params;
    private int $userId;
    private int $teamId;
    private string $title;
    private string $message;
    private ?string $imageUrl = null;
    private ?string $url = null;
    private ?int $badge = null;

    public function __construct(string $type)
    {
        $this->params = ["type" => $type];
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getBadge(): ?int
    {
        return $this->badge;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setTeamId(int $teamId)
    {
        $this->teamId = $teamId;
        return $this;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function setImageUrl(?string $imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function setUrl(?string $url)
    {
        $this->url = $url;
        return $this;
    }

    public function setBadge(?int $badge)
    {
        $this->badge = $badge;
        return $this;
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

        if (isset($this->url)) {
            $array["url"] = $this->url;
        }

        if (isset($this->badge)) {
            $array["badge"] = $this->badge;
        }

        return $array;
    }
}
