<?php

namespace Tymy\Module\Team\Model;

use JsonSerializable;

/**
 * Description of SimpleTeam
 */
class SimpleTeam implements JsonSerializable
{
    public string $sysName;
    public string $name;
    public string $sport;
    public array $languages;
    public string $defaultLanguageCode;

    public function setSysName(string $sysName): static
    {
        $this->sysName = $sysName;
        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setSport(string $sport): static
    {
        $this->sport = $sport;
        return $this;
    }

    /**
     * @param mixed[] $languages
     */
    public function setLanguages(array $languages): static
    {
        $this->languages = $languages;
        return $this;
    }

    public function setDefaultLanguageCode(string $defaultLanguageCode): static
    {
        $this->defaultLanguageCode = $defaultLanguageCode;
        return $this;
    }

    /**
     * @return array<string, string>|array<string, mixed[]>
     */
    public function jsonSerialize(): array
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
