<?php

namespace Tymy\Module\Team\Model;

use JsonSerializable;

/**
 * Description of SimpleTeam
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 6. 8. 2020
 */
class SimpleTeam implements JsonSerializable
{
    public string $sysName;
    public string $name;
    public string $sport;
    public array $languages;
    public string $defaultLanguageCode;

    public function setSysName(string $sysName)
    {
        $this->sysName = $sysName;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setSport(string $sport)
    {
        $this->sport = $sport;
        return $this;
    }

    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
        return $this;
    }

    public function setDefaultLanguageCode(string $defaultLanguageCode)
    {
        $this->defaultLanguageCode = $defaultLanguageCode;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            "sysName" => $this->sysName,
            "name" => $this->name,
            "sport" => $this->sport,
            "languages" => $this->languages,
            "defaultLanguageCode" => $this->defaultLanguageCode,
        ];
    }
}
