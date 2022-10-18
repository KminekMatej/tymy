<?php

namespace Tymy\Module\User\Model;

use JsonSerializable;

/**
 * Description of SimpleUser
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 8. 2020
 */
class SimpleUser implements JsonSerializable
{
    public function __construct(private string $id, private string $login, private ?string $callName, private string $pictureUrl, private ?string $gender, private string $status, private ?string $email)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getCallName(): ?string
    {
        return $this->callName;
    }

    public function getPictureUrl(): string
    {
        return $this->pictureUrl;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return array<string, string>|array<string, null>
     */
    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "login" => $this->login,
            "callName" => $this->callName,
            "pictureUrl" => $this->pictureUrl,
            "gender" => $this->gender,
            "status" => $this->status,
        ];
    }
}
