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
    private int $id;
    private string $login;
    private ?string $callName = null;
    private string $pictureUrl;
    private ?string $gender = null;
    private string $status;

    public function __construct(string $id, string $login, ?string $callName, string $pictureUrl, ?string $gender, string $status)
    {
        $this->id = $id;
        $this->login = $login;
        $this->callName = $callName;
        $this->pictureUrl = $pictureUrl;
        $this->gender = $gender;
        $this->status = $status;
    }

    public function jsonSerialize()
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
